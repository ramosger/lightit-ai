<!-- markdownlint-disable MD033 -->
<!-- markdownlint-disable-next-line MD041 -->

<h1 align="center">
<a href="https://lightit.io" target="_blank"><img src="https://lightit.io/images/Logo_purple.svg" width="400" alt="Light-it logo" /></a>
</h1>

<p align="center">
<strong>The official Light-it AI Stack Installer</strong>
</p>

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT"></a>
<img src="https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white" alt="PHP 8.4+">
<img src="https://img.shields.io/badge/platform-macOS%20%7C%20Linux-lightgrey" alt="Platform">
</p>

<!-- markdownlint-enable MD033 -->

---

## What It Does

`lightit-ai` is a TUI installer that bootstraps the Light-it AI development stack into your environment. It installs and manages four tools that together give Claude Code persistent memory, token efficiency, type safety, and vector search capabilities.

| Tool | What it does | Install method |
|------|-------------|----------------|
| **Engram** | Persistent memory across AI sessions | Homebrew (`gentleman-programming/tap`) |
| **RTK** | Token-optimized CLI proxy (60–90% token savings) | Homebrew or install script |
| **Pao** | PHP type safety enforcement tool | Composer global |
| **Leann** | Local vector search / semantic memory | pip (`leann-py`) |

---

## Installation via Homebrew (recommended)

```bash
brew tap ramosger/tap
brew install lightit-ai
```

To update:

```bash
brew upgrade lightit-ai
```

---

## Quick Start (binary)

Download the latest binary from the [releases page](https://github.com/ramosger/lightit-ai/releases/latest), then:

```bash
chmod +x lightit-ai
./lightit-ai
```

Requirements: PHP 8.4+

---

## Installation (from source)

Requirements: PHP >= 8.4 & Composer

```bash
# Clone and install dependencies
git clone https://github.com/ramosger/lightit-ai
cd lightit-ai
composer install

# Run directly
php application

# Or build a standalone binary
composer build          # outputs builds/lightit-ai
```

---

## TUI Screens

- **Install** — select and install any tools not yet present
- **Update** — check for and apply updates to installed tools
- **Providers** — provider-specific extras (Claude statusline via ccstatusline, OpenCode coming soon)
- **Engram** — launch Engram TUI and view memory statistics
- **Uninstall** — remove installed tools cleanly

---

## Development

```bash
# Run tests
composer test

# Lint
composer lint

# Build standalone binary
composer build
```
