<h1 align="center">Cheque API</h1>

### Общая информация
Предметная область: подсчет чеков
<br/>Суть проекта: подсчитать чеки и распределить товары в чеке по нескольким участникам.

### Используемые библиотеки
- в основе MVC фреймворк Laravel
- MediaLibrary - для облегчения работы с загрузкой файлов
- SanCtum - для облегчения работы с авторизацией

### Сниппет кода для подсчета долей:
php
$positions = Position::where('cheque_id', $cheque->id)->get();
$transactions = [];
foreach ($positions as $position)
{
$partitions = Partition::where('position_id', $position->id)->get();
foreach ($partitions as $partition)
{
$user = User::where('id', $partition->user_id)->first();
$amt = $position->sum / count($partitions);
if (!array_key_exists($user->id, $transactions))
{
$transactions[$user->id] = $amt;
}
else
{
$transactions[$user->id] += $amt;
}
}
}

### Требования

- Функциональные требования
  <br/>БД должна хранить:
    - список чеков
    - список позиций
    - список расчетов
    - список транзакций
    - список пользователей

  <br/>Сервер должен:
    - давать возможность загружать в него таблицы с чеками
    - выгружать подсчитанные доли
      <br/><br/>

- Нефункциональные требования
    - взаимодействие с БД
    - работа с csv-документами
    - поддержка объемных csv-документов
    - система авторизации
    - доступ к просмотру всеми авторизованными пользователями

### Архитектура согласно нотации С4
![alt text](https://github.com/SergStas/php_laravel_cheque_api/blob/master/ca1.jpg?raw=true)
![alt text](https://github.com/SergStas/php_laravel_cheque_api/blob/master/ca2.jpg?raw=true)
![alt text](https://github.com/SergStas/php_laravel_cheque_api/blob/master/ca3.jpg?raw=true)

### Диаграмма последовательности (sequence diagram)

![alt text](https://github.com/SergStas/php_laravel_cheque_api/blob/master/ca4.jpg?raw=true)