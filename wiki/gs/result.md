## Getting Started: Result
This page will explain the format of results you get from the API.

### General Result format
Most of the requests will respond with a JSON encoded result.  
Some API calls will result in a different result, like images or other files.  
You can read about this in the description of the functions.

### Specific JSON result
The basic result contains at least 3 fields, but typically there are at least 4 or 5 fields.
<pre>
{
	"status": 200,
	"message": "Ok",
	"apicode": 200,
	"log": [
		"User created successfully"
	],
	"runtime": "3.31ms"
}
</pre>
The `status`, `message` and `apicode` fields are always present. The `status` and `apicode` field currently contain the same values, but this might change in the future.  
They represent the HTTP status code. Additional information to this code in given in the `message` field. This can just be the "standard" HTTP message (like "Ok"), but it can give you also additional information in case of an error.

The `log`-field contains a log of the API-query-execution. It might be empty and typically it can be ignored. You can disable the transfer of the log by setting `sys_log_disable` to `true` in your request. In this case, the `log` field will **still be in the result**, but the array will be empty.

The `runtime`-field gives you information about how much time the server needed to execute your query.

### HTTP Response codes
The API will also add a HTTP response code header. The meaning of the codes are explained in the message field. They respect the definition of [RFC 7231](https://tools.ietf.org/html/rfc7231) and other RFCs. A list of statuscodes can be found [here](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes).

### Other Content-types
If the result is not JSON-formated, this information will be given in the content-type header.

---

You just finished the **Getting Started** section of this wiki. Maybe you want to return to the [Main Page](../README.md) and learn more about the modules and functions.