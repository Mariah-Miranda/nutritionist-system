-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 23 juil. 2025 à 17:08
-- Version du serveur : 10.4.25-MariaDB
-- Version de PHP : 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nutrition_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `patient_health_metrics`
--

CREATE TABLE `patient_health_metrics` (
  `metric_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `record_date` datetime NOT NULL DEFAULT current_timestamp(),
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `systolic_bp` int(11) DEFAULT NULL,
  `diastolic_bp` int(11) DEFAULT NULL,
  `blood_sugar_level_mg_dL` decimal(6,2) DEFAULT NULL,
  `blood_sugar_fasting_status` enum('Fasting (8+ hours)','Non-Fasting','Random') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `patient_health_metrics`
--

INSERT INTO `patient_health_metrics` (`metric_id`, `patient_id`, `record_date`, `weight_kg`, `bmi`, `systolic_bp`, `diastolic_bp`, `blood_sugar_level_mg_dL`, `blood_sugar_fasting_status`) VALUES
(2, 3, '2025-07-23 00:56:03', '45.00', '16.94', 110, 70, '80.00', 'Non-Fasting'),
(3, 3, '2025-07-23 00:56:32', '45.00', NULL, 110, 70, '80.00', 'Non-Fasting'),
(4, 8, '2025-07-23 13:59:05', '70.00', '24.51', 130, 70, '80.00', 'Non-Fasting');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `patient_health_metrics`
--
ALTER TABLE `patient_health_metrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `patient_health_metrics`
--
ALTER TABLE `patient_health_metrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `patient_health_metrics`
--
ALTER TABLE `patient_health_metrics`
  ADD CONSTRAINT `patient_health_metrics_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
