## Getting Started: Structure
This page will explain the basic structure of API calls.

----

Every API call is built up of a module name `mod` and a function name `fct`.  
Some calls might contain more specific function names, so called sub-functions `subfct` or even
`subsubfct`...

The [baseURL](../baseUrl.md) is contained in every query.

To build up the query-URL you just have to concatenate the module and the functions by `/`, i.e.  
`mod/fct` or `mod/fct/subfct` and so on.

---
### Example
Let `b` be the [baseURL](../baseUrl.md).  
To use the **Login** function of the **Auth**-module, you have to call `b/auth/login`

---

Of course, to perform a login to the system you have to specify a username and a password, in general:  
You have to use [parameters](parameters.md).

So continue the **Getting Started**-chapter by learning the usage of [parameters](parameters.md).