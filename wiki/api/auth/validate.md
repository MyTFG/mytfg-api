# Auth/Validate
Function **validate** of module **auth**

---

The validate functions checks if the given authentication token is still valid.

### Parameters
The request needs to contain the following parameters (this is a JSON-example):
<pre>
{
	"auth_sys_token": STRING
}
</pre>
(Basically this function does nothing except checking if the login was successful and returning this as its result).

### Result
#### Success
If the token is still valid, the status will be **200**.
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
<pre>
{
	"status": 401,
	"message": "The given Login is invalid",
	"apicode": 401,
	...
}
</pre>