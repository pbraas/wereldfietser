# Wereldfietser Login Extension

This phpBB extension provides an authentication provider that integrates with the Wereldfietser API. It allows users to log in with their Wereldfietser credentials and includes a flow for linking existing forum accounts to their API accounts.

## Features

*   **API-First Authentication:** Prioritizes the Wereldfietser API for user login.
*   **Database Fallback:** Falls back to the local phpBB database if the API login fails, ensuring administrators can always access the board.
*   **Automatic User Creation:** Creates new phpBB users on the fly if they do not exist locally.
*   **Account Linking:** Provides a user-friendly process for users with existing phpBB accounts to link them to their Wereldfietser API account.

## Technical Documentation

For a detailed explanation of the authentication logic and process flow, please see the **[Login Flow Documentation](docs/LOGIN_FLOW.md)**.

## Installation

1.  Copy the extension to: `phpBB/ext/wereldfietser/wereldfietser`
2.  In the ACP, go to: **Customise → Manage extensions**
3.  Enable the **Wereldfietser leden inlog** extension.
4.  Navigate to **ACP > Client communication > Authentication** and select "Wereldfietser leden inlog" as the authentication method.
5.  Manually create a Custom Profile Field with the "Field identification" set to `wereldfietser_id`.

## License

Licensed under the [GNU General Public License v2](license.txt)
