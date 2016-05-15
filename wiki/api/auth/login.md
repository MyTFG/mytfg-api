# Auth/Login
Function **login** of module **auth**

---

The login functions creates a new authentication token for the given user.  

### Parameters
The request needs to contain the following parameters (this is a JSON-example):
<pre>
{
	"username": STRING,
	"password": STRING,
	"device":   STRING,
	"session":  BOOLEAN
}
</pre>

### Result
#### Success
If the login was successful, the result will have status **200** and contain a `result`-field with an [Authentication](../../objects/authentication.md) object in it:

<pre>
{
	"status": 200,
	"message": "Ok",
	"apicode": 200,
	"log": [STRING],
	"result": AUTHENTICATION,
	"references": []
}
</pre>
The [references](../../gs/references.md) will always be empty.

#### Errors
If your request contains all needed parameters, there might be these error-codes:

| Code | Meaning |
| -----|:--------|
| 412  | The user is not activated |
| 401  | The given login is invalid (wrong password) |
| 400  | The user does not exist |