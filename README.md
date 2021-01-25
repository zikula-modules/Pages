# Simple pages management module

[![](https://github.com/zikula-modules/Pages/workflows/Generate%20module/badge.svg)](https://github.com/zikula-modules/Pages/actions?query=workflow%3A"Generate+module")
[![](https://github.com/zikula-modules/Pages/workflows/Test%20module/badge.svg)](https://github.com/zikula-modules/Pages/actions?query=workflow%3A"Test+module")

## Documentation

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Upgrading](#upgrading)
5. [Changelog](#changelog)
6. [TODO](#todo)
7. [Questions, bugs and contributing](#questions-bugs-and-contributing)

## Introduction

Pages is a Zikula module for managing content pages. It is kept simple and easy on purpose.

For a more comprehensive content management extension see [Content](https://github.com/zikula-modules/Content/).

## Requirements

The `main` branch of this module is intended for being used with Zikula 3.0.
For Zikula 2.0.x look at [releases](https://github.com/zikula-modules/Pages/releases/) and the [v3 branch](https://github.com/zikula-modules/Pages/tree/v3).

## Installation

The Pages module is installed like this:

1. Download the [latest release](https://github.com/zikula-modules/Pages/releases/latest).
2. Copy the content of `extensions/` into the `extensions/` directory of your Zikula installation. Afterwards you should a folder named `extensions/Zikula/PagesModule/`.
3. Initialize and activate ZikulaPagesModule in the extensions administration.

## Upgrading

### Upgrade to Pages 4.0.0 (Zikula 2.x to 3.x)

1. Ensure you have Zikula 2.x with Pages 3.2.3 running (download from the [this release](https://github.com/zikula-modules/Pages/releases/tag/3.2.3)).
2. Upgrade Zikula core to 3.x.
3. Delete the `modules/Zikula/PagesModule/` directory entirely.
4. Copy the content of `extensions/` into the `extensions/` directory of your Zikula installation. Afterwards you should a folder named `extensions/Zikula/PagesModule/`.
5. In `/.env.local` set `APP_DEBUG=1`.
6. **Create a backup of your database!**
7. Update ZikulaPagesModule in the extensions administration.
8. In `/.env.local` set `APP_DEBUG=0`.

In case something goes wrong:

1. Restore your database dump.
2. Report your problem in the issue tracker at <https://github.com/zikula-modules/Pages/issues> - in case you got an exception please post the complete stack trace.
3. Add the patch or follow the advice you got.
4. Update ZikulaPagesModule in the extensions administration again.

## Changelog

### Version 4.1.0

New features:

- None yet.

Bugfixes:

- Fixed combination of owner permission, private mode and only own flag.

### Version 4.0.0

Structural changes:

- Entirely rewritten for Zikula 3.0.x using ModuleStudio.

New features:

- Added `active` flag for disabling pages.
- Added support for RSS and Atom feeds.
- Added support for MultiHook needles.
- Added owner permission support so people may edit their own pages.

Deprecations:

- ...

## TODO

- ...

## Questions, bugs and contributing

If you want to report something or help out with further development of the Pages module please refer
to the corresponding GitHub project at <https://github.com/zikula-modules/Pages>.
