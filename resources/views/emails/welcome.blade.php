<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
</head>
<body>
    Hey {{$user->name}},
    Welcome to {{env('APP_NAME')}}.
</body>
</html>
