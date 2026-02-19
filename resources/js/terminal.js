import { Terminal } from 'xterm';
import { FitAddon } from 'xterm-addon-fit';
import 'xterm/css/xterm.css';

window.startXterm = function (containerId, executeRoute, autocompleteRoute, csrfToken) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // cleanup previous instance if any
    container.innerHTML = '';

    const term = new Terminal({
        cursorBlink: true,
        fontFamily: 'Menlo, Monaco, "Courier New", monospace',
        fontSize: 14,
        theme: {
            background: '#101828',
            foreground: '#05df72',
        }
    });

    const fitAddon = new FitAddon();
    term.loadAddon(fitAddon);

    term.open(container);
    fitAddon.fit();

    // Handle resizing
    window.addEventListener('resize', () => fitAddon.fit());

    // State
    let commandBuffer = '';
    let history = [];
    let historyIndex = -1;
    let cwd = '~'; // Default, will be updated by server response

    term.write('Welcome to the terminal. Type \x1b[1;36mhelp\x1b[0m for a list of commands.\r\n');
    prompt();

    function prompt() {
        term.write('\r\n\x1b[1;32m' + cwd + ' $\x1b[0m ');
    }

    term.onData(e => {
        switch (e) {
            case '\r': // Enter
                term.write('\r\n');
                executeCommand();
                break;
            case '\u007F': // Backspace (DEL)
                if (commandBuffer.length > 0) {
                    term.write('\b \b');
                    commandBuffer = commandBuffer.slice(0, -1);
                }
                break;
            case '\u0009': // Tab
                handleAutocomplete();
                break;
            // Arrow keys (ANSI escape sequences)
            case '\x1b[A': // Up
                if (history.length > 0) {
                    // simple history for now
                    if (historyIndex < history.length - 1) {
                        historyIndex++;
                        setCommand(history[history.length - 1 - historyIndex]);
                    }
                }
                break;
            case '\x1b[B': // Down
                if (historyIndex > -1) {
                    historyIndex--;
                    if (historyIndex === -1) {
                        setCommand('');
                    } else {
                        setCommand(history[history.length - 1 - historyIndex]);
                    }
                }
                break;
            default:
                if (e >= ' ' && e <= '~') {
                    term.write(e);
                    commandBuffer += e;
                }
        }
    });

    function setCommand(cmd) {
        // clear current line
        // Move to start of line, clear to right
        // But we have a prompt...
        // Naive erase:
        while (commandBuffer.length > 0) {
            term.write('\b \b');
            commandBuffer = commandBuffer.slice(0, -1);
        }
        term.write(cmd);
        commandBuffer = cmd;
    }

    async function executeCommand() {
        const cmd = commandBuffer.trim();
        commandBuffer = '';

        if (!cmd) {
            prompt();
            return;
        }

        if (cmd !== history[history.length - 1]) {
            history.push(cmd);
        }
        historyIndex = -1;

        try {
            const response = await fetch(executeRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    command: cmd,
                    cwd: cwd === '~' ? '/' : cwd // backend expects / initially or real path
                })
            });
            const data = await response.json();

            if (data.error) {
                term.write('\x1b[1;31mError: ' + data.error + '\x1b[0m');
            } else {
                // Formatting output? terminal output usually has \n, xterm needs \r\n
                // Simple regex replace
                const formatted = data.output.replace(/\n/g, '\r\n');
                term.write(formatted);
                if (data.newCwd) {
                    cwd = data.newCwd;
                }
            }
        } catch (e) {
            term.write('\x1b[1;31mClient-side error: ' + e.message + '\x1b[0m');
        }

        prompt();
    }

    async function handleAutocomplete() {
        if (!commandBuffer) return;

        try {
            // We need to resolve path to provide real CWD for autocomplete
            // For now use current cwd
            const response = await fetch(autocompleteRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    input: commandBuffer,
                    cwd: cwd
                })
            });
            const suggestions = await response.json();

            if (suggestions.length === 1) {
                const match = suggestions[0];
                // basic replace last word logic
                const parts = commandBuffer.split(' ');
                const last = parts.pop();
                // ... logic similar to previous alpine ...
                // simplified: just replace buffer with match if it's simple
                // This part needs to be robust, let's keep it simple: 
                // if match starts with last part, append suffix
                if (match.startsWith(last)) {
                    const suffix = match.substring(last.length);
                    term.write(suffix);
                    commandBuffer += suffix;
                }
            } else if (suggestions.length > 1) {
                term.write('\r\n' + suggestions.join('  ') + '\r\n');
                // reprint prompt
                term.write('\x1b[1;32m' + cwd + ' $\x1b[0m ' + commandBuffer);
            }
        } catch (e) {
            // ignore
        }
    }
};
