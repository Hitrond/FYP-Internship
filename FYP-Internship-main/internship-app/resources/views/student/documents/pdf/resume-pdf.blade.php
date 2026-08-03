<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0">
    @include('student.documents.partials.resume-document', [
        'user' => $user,
        'resume' => $resume,
        'template' => $template,
    ])
</body>
</html>
