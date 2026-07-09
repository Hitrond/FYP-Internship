<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cover Letter</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333333;
            margin: 40px;
        }
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .name {
            font-size: 24pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .contact-info {
            font-size: 10pt;
            color: #475569;
        }
        .recipient-info {
            margin-bottom: 30px;
        }
        .date {
            margin-bottom: 20px;
        }
        .body-text {
            text-align: justify;
            white-space: pre-wrap;
        }
        .signature {
            margin-top: 40px;
        }
    </style>
</head>
<body>

    @php
        $username = $profile?->user?->user_name ?? $user->user_name ?? $user->name ?? 'Your Name';
        $email = $profile?->personal_email ?? $user->email ?? 'email@example.com';
        $phone = $profile?->contact_number ?? $user->phone ?? '+60 12-345 6789';
        
        $manager = $coverLetter->hiring_manager ?: 'Hiring Manager';
        $company = $coverLetter->company_name ?: 'Company Name';
    @endphp

    <div class="header">
        <div class="name">{{ $username }}</div>
        <div class="contact-info">
            {{ $email }} | {{ $phone }}
        </div>
    </div>

    <div class="date">
        {{ $date }}
    </div>

    <div class="recipient-info">
        <strong>{{ $manager }}</strong><br>
        {{ $company }}<br>
        Malaysia
    </div>

    <div class="body-text">
Dear {{ $manager }},

{{ $coverLetter->body_text }}

Sincerely,

<strong>{{ $username }}</strong>
    </div>

</body>
</html>