# Security Policy

## Supported Versions

Only the latest release of **Inter Fonts** is actively maintained and receives security fixes.

| Version | Supported          |
|---------|--------------------|
| 2.x     | ✅ Yes              |
| < 2.0   | ❌ No               |

## Nextcloud Compatibility

This app targets **Nextcloud 32–35** with **PHP 8.2+**. Security fixes will only be issued for versions that are themselves supported by Nextcloud GmbH.

## Reporting a Vulnerability

If you discover a security vulnerability in this project, **please do not open a public GitHub issue**.

Instead, report it privately using one of the following methods:

- **GitHub Private Vulnerability Reporting** (preferred):  
  Use the [Report a vulnerability](https://github.com/solracsf/nc-interfonts/security/advisories/new) button available in the *Security* tab of this repository.

- **Direct contact**:  
  Reach out to the maintainer directly via GitHub: [@solracsf](https://github.com/solracsf)

### What to include in your report

To help assess and address the issue as quickly as possible, please provide:

- A clear description of the vulnerability and its potential impact
- Steps to reproduce the issue (proof of concept if applicable)
- Affected version(s) and environment details (Nextcloud version, PHP version, OS)
- Any suggested fix or mitigation if available

## Response Timeline

| Step                        | Target timeframe |
|-----------------------------|-----------------|
| Acknowledgement of report   | Within 48 hours |
| Initial assessment          | Within 7 days   |
| Fix or mitigation released  | Within 30 days  |

If a vulnerability requires more time to address, the maintainer will communicate progress to the reporter.

## Scope

This security policy covers the **Inter Fonts** Nextcloud app (`nc-interfonts`) only.

**In scope:**
- PHP code under `lib/`
- App metadata under `appinfo/`
- Font loading and injection mechanisms

**Out of scope:**
- The **Inter** typeface itself (maintained by [Rasmus Andersson](https://rsms.me/inter/))
- Vulnerabilities in Nextcloud core or its dependencies
- Issues in third-party servers or hosting environments

## Disclosure Policy

This project follows a **coordinated disclosure** approach. Once a fix is released, a public security advisory will be published on GitHub. Reporters are kindly asked to keep the vulnerability confidential until the fix is available.

## Credits

Security researchers who responsibly disclose valid vulnerabilities will be credited in the corresponding security advisory (unless they prefer to remain anonymous).
