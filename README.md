# Autoparts-ecommerce
[Apasă aici pentru a vedea un Video Demo cu site-ul](https://youtu.be/ezwGZjIOwXA)

Acesta este un magazin online de piese auto pe care l-am construit de la zero. Scopul principal al acestui proiect este aprofundarea conceptelor fundamentale din arhitectura web, interacțiunea dintre client și server, crearea și gestionarea bazelor de date.

## Arhitectura și Tehnologii Utilizate:
* Front-End: HTML5, CSS3, Bootstrap 5 pentru un design fluid și JavaScript.
* Back-End: PHP, cu o structură organizată a codului folosind principiul DRY (Don't Repeat Yourself).
* Baza de date: MySQL, care manipulează datele.

## Funcționalitățile Principale
* Sistem asincron pentru coșul de cumpărături: Adăugarea produselor în coș și actualizarea interfeței se realizează dinamic prin AJAX, fără reîncărcări de pagini.
* Filtrare dinamică în cascadă: Modul de căutare al pieselor este bazat pe compatibilitatea cu autovehiculul clientului, care poate fi selectat după Marca -> Model -> An -> Motorizare.
* Crearea unui cont: Sistem de gestiune a clienților care include stocarea parolelor criptate (funcția "password_hash").
* Organizarea modulară: Separarea codului repetitiv, cum ar fi header-ul și footer-ul, în fișiere distincte folosind funcția "include" din PHP pentru a face codul mult mai ușor de modificat.

## Integrarea Asistenței AI în fluxul de lucru
* Acest proiect este realizat cu ajutorul inteligenței artificiale (Gemini). Cu ajutorul acesteia am reușit să folosesc PHP pentru funcțiile vitale ale acestui site.
* Rolul meu: Am proiectat structura bazei de date, am definit logica de business, am modificat structuri de cod când a fost nevoie și alte modificări.
* Rolul AI-ului: Asistentul a fost utilizat pentru generarea sintaxei, pentru asistență în procesul de depanare.

## Instrucțiuni pentru rularea locală:
* Instalați un mediu de dezvoltare local (XAMPP).
* Accesați phpMyAdmin, creați o bază de date cu numele "piese_auto_db" și importați fișierul sql care se află în repository.
* Accesați proiectul din browser.

