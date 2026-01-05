Настройка Google Sheets API и Google Picker API
Этот проект позволяет выбирать таблицу через Google Picker и добавлять в неё данные с помощью Google Sheets API.
1. Настройка в Google Cloud Console
Для работы проекта необходимо создать и настроить проект на Google Cloud Console.
Шаг 1: Создание проекта
Перейдите в Google Cloud Console.
Создайте новый проект (кнопка "New Project").
Шаг 2: Включение API
Вам нужно включить три API. Перейдите в раздел "APIs & Services" > "Library" и активируйте:
Google Picker API (для выбора файлов).
Google Sheets API (для записи данных).
Google Drive API (необходим для корректной работы Picker).
Шаг 3: Настройка экрана согласия (OAuth Consent Screen)
Перейдите в "APIs & Services" > "OAuth consent screen".
Выберите тип External.
Заполните обязательные поля (имя приложения, email).
Важно (Scopes): Нажмите "Add or Remove Scopes" и добавьте:
.../auth/spreadsheets (для записи в таблицы).
.../auth/drive.file (для работы Picker).
В разделе "Test users" добавьте свой Gmail-адрес, иначе авторизация не сработает.
Шаг 4: Создание учетных данных (Credentials)
Перейдите в "APIs & Services" > "Credentials".
API Key: Нажмите "Create Credentials" > "API Key". Сохраните его (используется в JS для Picker).
OAuth Client ID: Нажмите "Create Credentials" > "OAuth client ID".
Тип приложения: Web application.
Authorized JavaScript origins: Добавьте http://localhost:8000.
Authorized redirect URIs: Добавьте http://localhost:8000/callback.php (или ваш файл авторизации).
Скачайте JSON-файл или сохраните Client ID и Client Secret.
2. Инициализация проекта и Composer
Для работы серверной части (PHP) необходимо установить официальную библиотеку Google Client.
Инициализация нового проекта:
Если у вас еще нет файла composer.json, выполните в корневой папке:
composer init
composer require google/apiclient:^2.15
Эта команда создаст папку vendor/ и файл autoload.php, который вы подключаете в начале ваших PHP-скриптов:
require_once 'vendor/autoload.php';

3. Частые ошибки в 2026 году
CORS Error: Возникает, если вы забыли добавить http://localhost:8000 в "Authorized JavaScript origins".
403 Forbidden: Проверьте, что при авторизации вы запрашиваете область видимости (scope) www.googleapis.com.
404 Not Found (JS): Убедитесь, что URL для fetch начинается с https://. Использование относительных путей (без протокола) заставляет браузер искать API на вашем локальном сервере.
Redirect URI Mismatch: Ссылка в настройках Cloud Console должна в точности совпадать с URL, на который Google возвращает пользователя после входа.


