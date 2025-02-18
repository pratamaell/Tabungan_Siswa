-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 10, 2025 at 01:28 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tabungan_siswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int NOT NULL,
  `nama_kelas` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `created_at`, `updated_at`) VALUES
(1, 'rpl 1', '2025-01-24 11:30:55', '2025-01-24 11:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `penarikan`
--

CREATE TABLE `penarikan` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nomor` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penarikan`
--

INSERT INTO `penarikan` (`id`, `siswa_id`, `nominal`, `tanggal`, `status`, `created_at`, `nomor`) VALUES
(3, 1, '5000.00', '2025-02-03 01:48:07', 'approved', '2025-02-03 01:51:15', '');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `tanggal`, `nominal`, `keterangan`, `created_at`) VALUES
(1, '2025-02-07', '40000.00', 'buat bayar sakit', '2025-02-07 07:02:37');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `kelas_id` int NOT NULL,
  `saldo` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `user_id`, `kelas_id`, `saldo`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '32000.00', '2025-01-24 11:31:14', '2025-02-07 04:03:47'),
(2, 5, 1, '497000.00', '2025-02-07 01:37:21', '2025-02-07 04:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `siswa_id` int NOT NULL,
  `nomor` text COLLATE utf8mb4_general_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `jenis` enum('setoran','penarikan') COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `siswa_id`, `nomor`, `nominal`, `jenis`, `tanggal`, `keterangan`, `created_at`, `updated_at`) VALUES
(25, 1, 'TRX-1737811971', '1000.00', 'setoran', '2025-01-25 20:32:00', 'hjjhj', '2025-01-25 13:33:02', '2025-01-25 13:33:02'),
(27, 1, 'TRX-1738553292', '20000.00', 'setoran', '2025-02-03 10:28:00', 'bayar kas februari', '2025-02-03 03:28:44', '2025-02-03 03:28:44'),
(28, 1, 'TRX-1738898808', '2000.00', 'setoran', '2025-02-07 10:26:00', 'bayar kas bulan juli', '2025-02-07 03:27:09', '2025-02-07 03:27:09'),
(29, 1, 'TRX-1738900198-341', '2000.00', 'setoran', '2025-02-07 10:50:00', 'byar kassss', '2025-02-07 03:50:13', '2025-02-07 03:50:13'),
(30, 2, 'TRX-1738900307-583', '12000.00', 'setoran', '2025-02-07 10:51:00', 'bayar kas', '2025-02-07 03:52:02', '2025-02-07 03:52:02'),
(31, 2, 'TRX-1738900307-583', '120000.00', 'setoran', '2025-02-07 10:51:00', 'bayar kas', '2025-02-07 03:54:27', '2025-02-07 03:54:27'),
(32, 2, 'TRX-1738900307-583', '120000.00', 'setoran', '2025-02-07 10:51:00', 'bayar kas', '2025-02-07 03:56:24', '2025-02-07 03:56:24'),
(33, 2, 'TRX-1738900307-583', '120000.00', 'setoran', '2025-02-07 10:51:00', 'bayar kas', '2025-02-07 03:58:42', '2025-02-07 03:58:42'),
(34, 1, 'TRX-1738901016', '12000.00', 'setoran', '2025-02-07 11:03:00', 'bayar kas', '2025-02-07 04:03:47', '2025-02-07 04:03:47'),
(35, 2, 'TRX-1738901047', '120000.00', 'setoran', '2025-02-07 11:04:00', 'ajhajakka', '2025-02-07 04:04:18', '2025-02-07 04:04:18'),
(36, 2, 'TRX-1738901362', '5000.00', 'setoran', '2025-02-07 11:09:00', 'sssksjjkjs', '2025-02-07 04:09:35', '2025-02-07 04:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','bendahara','siswa') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'pratama', 'hjhj@gmail.com', '$2y$10$pSnEYl3v3QPpamm0KSX96ufpOdsr1bqI1TgtPUT9XCtdvHDGEzha2', 'admin', '2025-01-24 03:37:50', '2025-01-24 03:37:50'),
(2, 'pratama', 'tama@gmail.com', '$2y$10$pEboQqVeWutVYcoC0.Domu8ipReZ2YcUO0xoXteJ6/7GdC2Dka5JC', 'bendahara', '2025-01-24 04:20:39', '2025-01-24 04:20:39'),
(3, 'putri', 'putri@gmail.com', '$2y$10$lljHCm4GGDjcVvzb5geaW.qiwuhbH3M8Djz5IuWPbNezkxphyby0G', 'bendahara', '2025-01-24 06:02:19', '2025-01-24 06:02:19'),
(4, 'fahri', 'al@gmail.com', '$2y$10$OJiHuH98RidZGZnSiNe9y.g353s/tF4DyWSSLiu8R2wjmBtAmiquq', 'siswa', '2025-01-24 11:30:10', '2025-01-27 10:04:15'),
(5, 'karim', 'karim@gmail.com', '$2y$10$X8DkahKrqwqlwKoLuXmvP.gEoGIpB.9TwY4XjG2dLzcIT4adlpwlu', 'siswa', '2025-02-07 01:37:21', '2025-02-07 01:37:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penarikan`
--
ALTER TABLE `penarikan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penarikan`
--
ALTER TABLE `penarikan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penarikan`
--
ALTER TABLE `penarikan`
  ADD CONSTRAINT `penarikan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
