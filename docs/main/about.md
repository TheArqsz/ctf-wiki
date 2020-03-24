# O CTFach

**CTF** to zawody z zakresu bezpieczeństwa komputerowego podczas, których przed uczestnikami stawia się pewne zadania. Wyróżnia się turnieje typu:

- Jeopardy (na tych skupiamy się w ramach tego projektu)
- Attack & Defence

Znane są także inne, mniej popularne rodzaje takie jak:

- King of the Hill
- mieszane

## Jeopardy CTF

Najpopularniejsza forma CTFów. Turniej podzielony jest na różnorakie kategorie - naczęściej występujące to:

- web
- pwn
- crypto
- misc
- re

Występują też inne takie jak `OSINT` (wyszukiwanie informacji w internecie i nie tylko) czy `TRIVIA`, ale nie są one tak popularne jak te wymienione wcześniej.

### Flaga

Każda z kategorii zawiera inny typ zadań, ale ich cel jest taki sam - znaleźć `flagę`. Może ona przybierać rozmaite formy, ale najczęściej jest to
```
skrót turnieju + '{' + ciąg znaków + '}'
```
Znaki pomiędzy `{}` reprezentowane mogą być przez:

- losowo dobrany, odpowiednio długi ciąg
- zdanie zapisane w formacie [leet speak](http://www.robertecker.com/hp/research/leet-converter.php)
- zdanie w dowolnym języku, ale zamiast `spacji` mamy `_`
- co tylko wymyślą autorzy zadania

Jeśli ciąg ten jest zrozumiałym tekstem to zazwyczaj jego treść związana jest ze sposobem rozwiązania zadania.

Weźmy na przykład turniej [**Break The Syntax CTF 2019**](https://pwrwhitehats.github.io/bts/bts-1st-edition/) i zadanie z kategorii webowej [**Asterix Gallery**](https://github.com/TheArqsz/Writeups-BtS-CTF/tree/master/AsterixGallery).

Skrót turnieju to `BtS-CTF`. Zadanie oparte było (w duzym uproszczeniu) na manipulacji nagłówkami http odpowiedzi od serwera. Ostateczna forma flagi to 
```
BtS-CTF{y0u_f0und_my_h34d3r5_6r347_w0rk}
```

### O kategoriach

Szczegółowy opis każdej z kategorii:
 
- [web](/web/about/) 
- [pwn](/pwn/about/)
- [crypto](/crypto/about/)
- [misc](/misc/about/)
- [re](/re/about/)

## Attack and Defence

Ten typ zawodów jest zdecydowanie mniej popularny, ale nie znaczy to, że mniej ciekawy. Taki turniej jest trudniejszy do zorganizowania ze względu na duże wymagania infrastrukturalne, dlatego rzadko kiedy spotyka się tą formę w wersji online. 

Gracze dostają taki sam zestaw środowisk i mają za zadanie jak najlepiej je zabezpieczyć.

Celem jest obrona własnej maszyny, ataki na środowiska innych drużyn i utrzymanie się jak najdłużej w stawce. 

Najsłynniejsze tego typu wydarzenia to:

- [iCTF](https://ictf.cs.ucsb.edu/)
- [DEF CON CTF](https://blog.legitbs.net/2017/07/def-con-ctf-2017-final-scores-and-data.html)