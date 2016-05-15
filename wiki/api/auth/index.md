# Auth
Authentication Module

---

This module provides the functions to authenticate users to the API and manage those authentications.  


### Overview
| Function		| Description  |
|---------------------- | :------------ |
| [Login](login.md)     | Creates a new authentication token for a device |
| [Logout](logout.md)   | Deletes the authentication for a device |
| [Validate](validate.md)     | Checks whether a given authentication is still valid |


## The Authentication Process
The functions mentioned above are used to perform several steps of the authentication process.  
This section will give you an overview over this authenticaion process.

### Authentications
To authenticate a user to the API you have to use the username and the password. Additionally, you need to specify a *device*. The device is for the user to know on which device the login is used, so he can deactivate it if needed.

When you specify a correct username/password combination you will obtain a **token**. This is unique for every user. This token is used in all further requests to authenticate the user.

For some purposes it might be useful to have the token expire at some point (If you use a browser or public device). For this you have to specify the `session` parameter in your login - `true` means that you want the token to expire (after 30 minutes), `false` means the token will be valid for unlimited time until it is deleted manually (logout / user managment).

When you have the token you should keep it at a safe place (at least the unlimited tokens). The owner of a token has full control over the user account with all its permissions.

Thus, you should not use GET-parameter authentication in your browser app - everyone later can see your token.

### Special request parameters
With all requests that need a user authentication you need to pass the token of the user. This is done via the special field `sys_auth_token`.

If this parameter is given, the user will be automatically authenticated. For devices which can handle sessions (e.g., browsers) you might want to transmit the token only on your very first request, since the token is also stored in the `SESSION` on the server.

But for simplicity, you can of course set this parameter for every call.

### Login vs validate
Short answer: The **login** function creates a new authentication / token. All other authentications are untouched. Only use this function if you don't already have a valid token.

If you already obtained a token and are not sure if it is still valid, you can use the **validate** function. It will tell you, if the token is active and can be used for other requests.  
This can be used after a restart of your application or something like this.

### Logout?
Yes please. If you use a token which is valid for unlimited time, you should delete this token at some point. If possible, do not just request a new token while leaving an unused valid token. This might be a possible security risk.

Time-limited tokens will get deleted after a while, you don't have to worry about them.