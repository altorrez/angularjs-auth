# angularjs-auth
<h1>AngularJS Authentication / Login system</h1>

<p>An example of how to handle account creation and session handling with AngularJS. 
This example uses the <a href="https://github.com/altorrez/albus" target="_blank">Albus REST framework</a></p>

<p>Sessions are handled server side through a cookie, when angular is initialized it will check if a valid cookie exists and retrieve login information.</p>

<h2>Features</h2>
<ul>
  <li>BCrypt hashing of passwords</li>
  <li>Session handling</li>
  <li>Validation</li>
  <li>Error reporting</li>
</ul>
<p>This example includes mysql tables in api/albus/config/database.sql. Change the api/albus/Config/database.php file to be configured with your database.</p>
