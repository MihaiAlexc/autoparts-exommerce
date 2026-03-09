# autoparts-ecommerce

Acesta este un magazin online de piese auto pe care l-am contruit de la zero. Scopul principal al acestui proiect este aprofundarea conceptelor fundamentale din arhitectura web, interactiunea dintre client si server, crearea si gestionarea bazelor de date.

  Arhitectura si Tehnologii Utilizate:
Front-End: HTML5, CSS3, Bootstrap 5 pentru un design fluid si JavaScript.
Back-End: PHP, cu o structura organizata a codului folosind principiul DRY (Don't Repeat Yourself).
Baza de date: MySQL, care manipuleaza datele.

  Functionalitatile Principale
Sistem asincron pentru cosul de cumparaturi: Adaugarea produselor in cos si actualizarea interfetei se reralizeaza dinamic prin AJAX, fara anumite reincarcari de pagini.
2.Filtrare dinamica in cascada: Modul de cautare al pieselor este bazat pe combatibilitatea cu autovehiculul clinetului care poate fi selectat dupa Marca -> Model -> An -> Motorizare).
Creazrea unui cont: Sistem de gestiune a clientilor care include stocarea parolelor criptate (functia "password_hash").
Organizarea modulare: Separarea codului repetitiv cum ar fi header-ul si footer-ul in fisiere distincte folosind functia "include" din PHP pentru a face codul mult mai usor de modificat.

  Integrarea Asistenti AI in fluxul de lucru
Acest proiect este realizat cu ajutorul inteligentei artificiale (Gemini). Cu ajutorul acesteia am reusit sa folosesc PHP pentru functiile vitale ale acestui site.
Rolul meu: Am proiectat structura bazei de date, am definit logica de business, am modificat structuri de cod cand a fost nevoie, si alte modificari
Rolul AI-ului: Asistentul a fost utilizat pentru generarea sintaxei, pentru asistenta in procesul de depanare.

  Intructiuni pentru rularea locala:
Instalati un mediu de dezvoltare local (XAMPP)
Accesati phpMyAdmin, creati o baza de date cu numele "piese_auto_db' si importati fisierul sql care se afla in repository.
Accesati proiectul din browser.
