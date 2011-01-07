
<form method="post" action="<? echo $_SERVER['PHP_SELF'] ?>">
            <label>Username:</label>
            <input name="username" type="text">
            <br>
            
            <label>Password:</label>
            <input name="password" type="password">
            <br>
            
            <label>Re-enter Password:</label>
            <input name="password2" type="password">
            <br>
            
            <label>Email: </label>
            <input name="email" type="text">
            <br>
            
            <label>Group: </label>
            <select name="group_id">
                <option value="1">User</option>
                <option value="2">Developer</option>
                <option value="3">Designer</option>
            </select>
            <br>
            
            <input value="Register" type="submit">
        </form>

