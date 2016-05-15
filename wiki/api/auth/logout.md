# Auth/Logout
Function **logout** of module **auth**

---

Deletes the currently active token (token used for this request).

### Parameters
The request needs to contain the following parameters (this is a JSON-example):
<pre>
{
	"auth_sys_token": STRING
}
</pre>

### Result
#### Success
If the token was valid before (not expired) the status will be **200**.
<pre>
{
	"status": 200,
	"message": "Ok",
	"apicode": 200,
	...
}
</pre>

#### Errors
If the token is not valid (timed out or deleted / never created), the status will be **401**.
This can only happen, if the login failed with this token ([validate](validate.md)).
<pre>
{
	"status": 401,
	"message": "The given Login is invalid",
	"apicode": 401,
	...
}
</pre>
