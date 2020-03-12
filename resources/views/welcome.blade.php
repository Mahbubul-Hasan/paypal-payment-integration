<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <form action="{{ route('create-paypal-payment') }}" method="post">
            @csrf
            <button type="submit" style="cursor: pointer; border: 0;"><img src="{{asset('/asset/img/paypal.png')}}"/></button>
        </form>
    </div>
</body>
</html>