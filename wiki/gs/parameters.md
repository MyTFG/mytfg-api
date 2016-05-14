## Getting Started: Parameters
This page will explain the usage of parameters with API calls.

---

Many actions you want to perform require the usage of **parameters**.  
You have to pass these with your request to the API.

To do so, there are several possibilities:

* GET request
* POST requests with form-data
* POST requests with raw, json-encoded body

You might want to combine some of these possibilities, for example you can use GET-parameters and POST-parameters at the same time: They will be combined. But make sure to pass your parameter **either** via POST **or** via GET.  
If you pass a parameter multiple times with different values, the API will randomly pick one of them.

### GET-parameters
They are very useful for small queries or to obtain some kind of a REST-API.  
Also, you don't have to handle a POST-request on your client. But for larger requests with multiple parameters you might want to use one of the other possibilites:

### POST with form-data
This is the classic way of forms used on websites. You post the variables directly to the server, but you don't have to care about the encoding and decoding.  
This is very useful for the most of your requests.  
But for even bigger request, **json** could be the best choice.

### POST with raw, json-encoded body
Instead of passing single parameters via form-data you can pass a JSON-object (or Array) to the API.  
This is very useful for creating objects with many parameters or sub-parameters like user-creation.

### Combination
You can combine GET and POST parameters, but you cannot use POST with form-data and json-encoded body at the same time.

---

### Example
We skip the [baseUrl](../baseURL.md) in this example.
#### 1. Login via GET
In this example we do a simple login (not session-based) via GET:  
<pre>
/auth/login?username=test&password=mypass&device=myDeviceId
</pre>

#### 2. Login via JSON-body
To get the same result as in the first request, you have to pass the following JSON-body to the API:  
<pre>
{
	"username": "text",
	"password": "mypass",
	"device": "myDeviceId"
}
</pre>

---

### Conclusion
The best way to pass values might be to use the JSON-body, but for smaller requests or to directly use HTML-forms the usage of GET and form-data POST might be better.

The next step in the **Getting Started**-section is the [result](result.md) obtained from the API.