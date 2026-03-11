-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: mart. 09, 2026 la 05:59 PM
-- Versiune server: 10.4.32-MariaDB
-- Versiune PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Bază de date: `piese_auto_db`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `admini`
--

CREATE TABLE `admini` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `admini`
--

INSERT INTO `admini` (`id`, `username`, `password`) VALUES
(2, 'admin', '$2y$10$wHdynbeI38WUdE8HJtayv.NFzfwiVz5xGC1TeSgsOVPYOAi3qOgs6');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `anvelope_detalii`
--

CREATE TABLE `anvelope_detalii` (
  `id` int(11) NOT NULL,
  `id_produs` int(11) NOT NULL,
  `latime` varchar(10) NOT NULL,
  `inaltime` varchar(10) NOT NULL,
  `raza` varchar(10) NOT NULL,
  `sezon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `clienti`
--

CREATE TABLE `clienti` (
  `id` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `adresa` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `data_inregistrare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `clienti`
--

INSERT INTO `clienti` (`id`, `nume`, `email`, `telefon`, `adresa`, `password`, `data_inregistrare`) VALUES
(1, 'andrei', 'alexsplendit@gmail.com', '0723995573', 'dsaf', '$2y$10$AehNAjcjbS0kZy/KNa8W9ui/2ZqPjVQyJ/fsQ2OU.fPYx1tniLPmi', '2026-02-13 07:28:24'),
(2, 'alex', 'test@test.ro', '', '', '$2y$10$MzR3EgPJd8IIKiNw9z8y3uJnE4yHV2GuIJforGmblTM3SoqaWrMN2', '2026-03-07 22:39:27');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `comenzi`
--

CREATE TABLE `comenzi` (
  `id` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `nume_client` varchar(100) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `adresa` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `data_comanda` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'primita'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `comenzi`
--

INSERT INTO `comenzi` (`id`, `id_client`, `nume_client`, `telefon`, `adresa`, `total`, `data_comanda`, `status`) VALUES
(6, 1, 'daf', 'fdsa', 'fdsa', 425.10, '2026-03-01 09:00:26', 'primita'),
(7, NULL, 'cfvdqaf', 'fdsaf', 'fdsa', 309.40, '2026-03-01 10:03:20', 'primita'),
(8, NULL, 'dasf', 'fdsa', 'fdas', 309.40, '2026-03-01 10:07:41', 'primita'),
(9, 2, 'alex', '0723995573', 'asdf', 309.40, '2026-03-07 23:40:09', 'primita');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `compatibilitati`
--

CREATE TABLE `compatibilitati` (
  `id` int(11) NOT NULL,
  `id_produs` int(11) DEFAULT NULL,
  `id_masina` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `compatibilitati`
--

INSERT INTO `compatibilitati` (`id`, `id_produs`, `id_masina`) VALUES
(1, 8, 4),
(2, 9, 4),
(3, 10, 5);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `detalii_comanda`
--

CREATE TABLE `detalii_comanda` (
  `id` int(11) NOT NULL,
  `id_comanda` int(11) DEFAULT NULL,
  `id_produs` int(11) DEFAULT NULL,
  `cantitate` int(11) DEFAULT NULL,
  `pret` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `detalii_comanda`
--

INSERT INTO `detalii_comanda` (`id`, `id_comanda`, `id_produs`, `cantitate`, `pret`) VALUES
(7, 6, 10, 1, 425.10),
(8, 7, 9, 1, 309.40),
(9, 8, 9, 1, 309.40),
(10, 9, 9, 1, 309.40);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `favorite`
--

CREATE TABLE `favorite` (
  `id` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_produs` int(11) NOT NULL,
  `data_adaugare` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `favorite`
--

INSERT INTO `favorite` (`id`, `id_client`, `id_produs`, `data_adaugare`) VALUES
(1, 1, 9, '2026-03-06 23:20:12'),
(2, 1, 4, '2026-03-06 23:20:36');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `masini`
--

CREATE TABLE `masini` (
  `id` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `an_fabricatie` varchar(30) NOT NULL,
  `combustibil` varchar(30) NOT NULL,
  `motorizare` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `masini`
--

INSERT INTO `masini` (`id`, `marca`, `model`, `an_fabricatie`, `combustibil`, `motorizare`) VALUES
(1, 'Volkswagen', 'Golf VII', '2012-2020', 'Diesel', '2.0 TDI (150 CP)'),
(2, 'Volkswagen', 'Golf VII', '2012-2020', 'Benzina', '1.4 TSI (125 CP)'),
(3, 'BMW', 'Seria 3 (E90)', '2005-2011', 'Diesel', '320d (163 CP)'),
(4, 'BMW', 'Seria 3 (E90)', '2005-2011', 'Benzina', '320i (150 CP)'),
(5, 'Dacia', 'Logan II', '2012-2020', 'Benzina', '0.9 TCe (90 CP)'),
(6, 'Dacia', 'Logan II', '2012-2020', 'Diesel', '1.5 dCi (75 CP)');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `produse`
--

CREATE TABLE `produse` (
  `id` int(11) NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  `nume_piesa` varchar(100) NOT NULL,
  `descriere` text DEFAULT NULL,
  `cod_piesa` varchar(50) DEFAULT NULL,
  `pret_achizitie` decimal(10,2) NOT NULL DEFAULT 0.00,
  `adaos` int(11) NOT NULL DEFAULT 0,
  `tva` int(11) NOT NULL DEFAULT 19,
  `pret` decimal(10,2) NOT NULL,
  `stoc` int(11) DEFAULT 0,
  `imagine` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `produse`
--

INSERT INTO `produse` (`id`, `categorie`, `nume_piesa`, `descriere`, `cod_piesa`, `pret_achizitie`, `adaos`, `tva`, `pret`, `stoc`, `imagine`) VALUES
(3, 'anvelope', 'anvelope michelin', 'anvelope michelin aderenta maxima daca sunt folosite cu skoda octavia 1.4 tsi 150cp 250nm, cutie automata dsg 7 rapoarte, interior crem ', '1234', 1000.00, 13, 19, 1344.70, 4, '1771594146_WhatsApp Image 2026-02-20 at 11.28.20.jpeg'),
(4, 'uleiuri', 'ulei', 'fadsfasdf ads f', '111', 50.00, 30, 19, 77.35, 5, '1771594272_ulei prostf.avif'),
(5, 'suspensie', 'suspensi', 'dfjhawif;hjdjkajfkldsa;jf', '4325', 100.00, 29, 19, 153.51, 3, '1771880816_fundal-auto.avif'),
(7, 'suspensie', 'asdf', 'fdsaf', 'fdsaf', 123.00, 30, 19, 190.28, 23, '1771934593_1771594272_ulei prostf.avif'),
(8, NULL, 'adsf', 'dfsaf', 'dfsaf', 123.00, 30, 19, 190.28, 123, '1771936445_1770967636_WhatsApp Image 2026-01-27 at 08.54.45.jpeg'),
(9, 'frane', 'disc de frana', 'adfadsfdsaf', '54321', 200.00, 30, 19, 309.40, 13, '1772204251_Screenshot 2026-02-13 152812.png'),
(10, 'frane', 'd9sdc fraja', '14325235', '123566', 300.00, 30, 9, 425.10, 0, '1772228902_Screenshot 2025-05-14 154757.png');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `vizitatori`
--

CREATE TABLE `vizitatori` (
  `id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `data_vizita` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `vizitatori`
--

INSERT INTO `vizitatori` (`id`, `ip`, `data_vizita`) VALUES
(1, '::1', '2026-02-24'),
(16, '::1', '2026-02-26'),
(23, '::1', '2026-02-27'),
(42, '::1', '2026-03-01'),
(82, '::1', '2026-03-02'),
(85, '::1', '2026-03-06'),
(128, '::1', '2026-03-07'),
(160, '::1', '2026-03-09');

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `admini`
--
ALTER TABLE `admini`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `anvelope_detalii`
--
ALTER TABLE `anvelope_detalii`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produs` (`id_produs`);

--
-- Indexuri pentru tabele `clienti`
--
ALTER TABLE `clienti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexuri pentru tabele `comenzi`
--
ALTER TABLE `comenzi`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `compatibilitati`
--
ALTER TABLE `compatibilitati`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produs` (`id_produs`),
  ADD KEY `id_masina` (`id_masina`);

--
-- Indexuri pentru tabele `detalii_comanda`
--
ALTER TABLE `detalii_comanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_comanda` (`id_comanda`),
  ADD KEY `id_produs` (`id_produs`);

--
-- Indexuri pentru tabele `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unic_fav` (`id_client`,`id_produs`);

--
-- Indexuri pentru tabele `masini`
--
ALTER TABLE `masini`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `produse`
--
ALTER TABLE `produse`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `vizitatori`
--
ALTER TABLE `vizitatori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unic_vizitator` (`ip`,`data_vizita`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `admini`
--
ALTER TABLE `admini`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pentru tabele `anvelope_detalii`
--
ALTER TABLE `anvelope_detalii`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pentru tabele `clienti`
--
ALTER TABLE `clienti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pentru tabele `comenzi`
--
ALTER TABLE `comenzi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pentru tabele `compatibilitati`
--
ALTER TABLE `compatibilitati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pentru tabele `detalii_comanda`
--
ALTER TABLE `detalii_comanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pentru tabele `favorite`
--
ALTER TABLE `favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pentru tabele `masini`
--
ALTER TABLE `masini`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pentru tabele `produse`
--
ALTER TABLE `produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pentru tabele `vizitatori`
--
ALTER TABLE `vizitatori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- Constrângeri pentru tabele eliminate
--

--
-- Constrângeri pentru tabele `anvelope_detalii`
--
ALTER TABLE `anvelope_detalii`
  ADD CONSTRAINT `fk_anvelopa_produs` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`) ON DELETE CASCADE;

--
-- Constrângeri pentru tabele `compatibilitati`
--
ALTER TABLE `compatibilitati`
  ADD CONSTRAINT `compatibilitati_ibfk_1` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`),
  ADD CONSTRAINT `compatibilitati_ibfk_2` FOREIGN KEY (`id_masina`) REFERENCES `masini` (`id`);

--
-- Constrângeri pentru tabele `detalii_comanda`
--
ALTER TABLE `detalii_comanda`
  ADD CONSTRAINT `detalii_comanda_ibfk_1` FOREIGN KEY (`id_comanda`) REFERENCES `comenzi` (`id`),
  ADD CONSTRAINT `detalii_comanda_ibfk_2` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
