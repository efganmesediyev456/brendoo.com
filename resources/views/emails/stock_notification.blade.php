<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Уведомление о наличии</title>
</head>
<body>
<h2>Здравствуйте, {{ $customer->name }}!</h2>

<p>
    Товар <strong>{{ $product->title }}</strong> с вариантом
    <strong>{{ $option->filter->title }}: {{ $option->title }}</strong> теперь в наличии.
</p>

<p>Вы можете оформить заказ на нашем сайте.</p>

<p>Если у вас есть вопросы, пожалуйста, свяжитесь с нами.</p>

<br>
<p>С уважением,<br>Команда Brendoo</p>
</body>
</html>
