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
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(0, 2, '2025-07-22', '11:22:00', 'doc', 'Scheduled', '2025-07-21 11:23:04', '2025-07-21 11:23:04'),
(0, 1, '2025-07-21', '12:23:00', 'see the doc', 'Scheduled', '2025-07-21 11:23:40', '2025-07-21 11:23:40'),
(0, 3, '2025-07-21', '14:22:00', 'see the doc', 'Scheduled', '2025-07-21 11:56:09', '2025-07-21 11:56:09'),
(0, 2, '2025-07-21', '14:28:00', 'See the doc', 'Scheduled', '2025-07-21 12:28:33', '2025-07-21 12:28:33'),
(0, 2, '2025-07-29', '21:54:00', 'gh', 'Scheduled', '2025-07-21 18:54:49', '2025-07-21 18:54:49'),
(0, 1, '2025-07-25', '16:42:00', 'see the doc', 'Scheduled', '2025-07-23 13:42:56', '2025-07-23 13:42:56'),
(0, 1, '2025-07-25', '16:42:00', 'see the doc', 'Scheduled', '2025-07-23 13:43:23', '2025-07-23 13:43:23'),
(0, 3, '2025-07-24', '16:43:00', 'doc', 'Scheduled', '2025-07-23 13:44:02', '2025-07-23 13:44:02'),
(0, 1, '2025-07-23', '15:45:00', 'doc', 'Scheduled', '2025-07-23 13:45:05', '2025-07-23 13:45:05'),
(0, 1, '2025-07-25', '16:46:00', 'gsh', 'Scheduled', '2025-07-23 13:46:48', '2025-07-23 13:46:48'),
(0, 1, '2025-08-03', '16:49:00', 'doc', 'Scheduled', '2025-07-23 13:50:01', '2025-07-23 13:50:01'),
(0, 1, '2025-07-31', '13:53:00', 'g', 'Scheduled', '2025-07-23 13:54:01', '2025-07-23 13:54:01'),
(0, 3, '2025-07-26', '18:54:00', 'doc', 'Scheduled', '2025-07-23 13:54:40', '2025-07-23 13:54:40');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
