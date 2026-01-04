приложение работает в ограниченном режиме, чтобы снять ограничения, приложение должно быть зарегистрировано на организацию, а не частное лицо 

Возможно, понадобится поменять разрешения для приложения в index.php, redirect.php - 
client->addScope()
.../auth/drive.file	Yes	Only those created/opened by your app	Non-sensitive (Easy) - самая безопасная опция, можно открывать только таблицы, созданные в приложении или открытые пользователем при помощи Google.picker
.../auth/spreadsheets	Yes	All spreadsheets in the user's Drive	Sensitive (Moderate) - дает разрешение на чтение и изменение всех таблиц пользователя на Google drive
.../auth/drive	Yes	All files in the user's Drive	Restricted (Hard) - дает разрешение на чтение и изменение всех файлов