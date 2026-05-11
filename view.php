<?php
// Simple PHP view template
?>
<!DOCTYPE html>
<html>
<head>
    <title>Page</title>
</head>
<body>
    <h1>Welcome</h1>
    <p><?php echo isset($message) ? htmlspecialchars($message) : 'Hello World'; ?></p>
</body>
</html>