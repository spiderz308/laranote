---
paths:
  - composer.json
---

# General

## PHP, composer, npm are not on PATH — use Herd binaries
In the PowerShell shell, php, composer, npm, git and cmd are NOT on PATH. Use absolute paths: php = C:\Users\Admin\.config\herd\bin\php84\php.exe, composer.phar = C:\Users\Admin\.config\herd\bin\composer.phar (run `php composer.phar ...`), node/npm = C:\Users\Admin\.config\herd\bin\nvm\v24.20.0\. To execute a command (native output can't be piped with `&` here), use Start-Process with -RedirectStandardOutput/-RedirectStandardError to a temp file, or call via the call operator without a pipe. Set-ExecutionPolicy -Scope Process Bypass is required to run .ps1 scripts.
