-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 24, 2025 at 06:10 AM
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
(1, 'rpl 1', '2025-01-24 11:30:55', '2025-01-24 11:30:55'),
(2, 'titl2', '2025-02-18 04:52:36', '2025-02-18 04:52:36');

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
  `nomor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penarikan`
--

INSERT INTO `penarikan` (`id`, `siswa_id`, `nominal`, `tanggal`, `status`, `created_at`, `nomor`) VALUES
(7, 1, '5000.00', '2025-02-10 02:52:01', 'approved', '2025-02-10 02:52:01', 'TRX67a969d17609a'),
(8, 2, '2000.00', '2025-02-10 03:15:58', 'approved', '2025-02-10 03:15:58', 'TRX67a96f6e0bfb2'),
(9, 2, '2000.00', '2025-02-10 03:18:12', 'rejected', '2025-02-10 03:18:12', 'TRX67a96ff499586'),
(10, 2, '2000.00', '2025-02-10 03:20:25', 'approved', '2025-02-10 03:20:25', 'TRX67a9707995e79'),
(11, 2, '1000.00', '2025-02-10 03:36:26', 'approved', '2025-02-10 03:36:26', 'TRX67a9743a8903c'),
(12, 2, '1000.00', '2025-02-10 03:39:18', 'approved', '2025-02-10 03:39:18', 'TRX67a974e6de060'),
(13, 1, '5000.00', '2025-02-10 03:40:18', 'approved', '2025-02-10 03:40:18', 'TRX67a9752218070'),
(14, 1, '1000.00', '2025-02-10 03:42:12', 'approved', '2025-02-10 03:42:12', 'TRX67a975945cd5a'),
(15, 1, '1000.00', '2025-02-10 03:42:37', 'approved', '2025-02-10 03:42:37', 'TRX67a975ad36e7b'),
(16, 1, '1000.00', '2025-02-10 03:44:10', 'approved', '2025-02-10 03:44:10', 'TRX67a9760a681f6'),
(17, 1, '1000.00', '2025-02-10 03:49:20', 'approved', '2025-02-10 03:49:20', 'TRX67a9774001488'),
(18, 1, '1000.00', '2025-02-10 03:50:18', 'approved', '2025-02-10 03:50:18', 'TRX67a9777a74b0f'),
(19, 1, '1000.00', '2025-02-10 03:52:42', 'approved', '2025-02-10 03:52:42', 'TRX67a9780a2ef2d'),
(20, 1, '1000.00', '2025-02-10 03:53:42', 'approved', '2025-02-10 03:53:42', 'TRX67a97846b8d97'),
(21, 1, '1000.00', '2025-02-10 03:57:02', 'approved', '2025-02-10 03:57:02', 'TRX67a9790e31204'),
(22, 1, '1000.00', '2025-02-10 03:59:02', 'approved', '2025-02-10 03:59:02', 'TRX67a97986b8597'),
(23, 1, '1000.00', '2025-02-10 03:59:50', 'approved', '2025-02-10 03:59:50', 'TRX67a979b66dd96'),
(24, 1, '2000.00', '2025-02-10 04:02:00', 'approved', '2025-02-10 04:02:00', 'TRX67a97a3845ff5'),
(25, 1, '2000.00', '2025-02-10 06:25:24', 'approved', '2025-02-10 06:25:24', 'TRX67a99bd4deabe'),
(26, 1, '1000.00', '2025-02-10 06:26:07', 'approved', '2025-02-10 06:26:07', 'TRX67a99bff269ed'),
(27, 2, '1000.00', '2025-02-11 04:51:16', 'approved', '2025-02-11 04:51:16', 'TRX67aad744f179b'),
(28, 2, '1000.00', '2025-02-11 06:07:14', 'approved', '2025-02-11 06:07:14', 'TRX67aae912b8319'),
(29, 2, '2000.00', '2025-02-11 07:05:02', 'rejected', '2025-02-11 07:05:02', 'TRX67aaf69eb9a8b'),
(30, 2, '2000.00', '2025-02-18 04:08:03', 'approved', '2025-02-18 04:08:03', 'TRX67b407a32c44f');

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
(2, '2025-02-18', '2000.00', 'beli permen', '2025-02-18 04:23:47');

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
(1, 4, 1, '33000.00', '2025-01-24 11:31:14', '2025-02-18 04:26:40'),
(2, 5, 1, '12000.00', '2025-02-07 01:37:21', '2025-02-18 04:10:48'),
(3, 6, 2, '30000.00', '2025-02-24 03:35:11', '2025-02-24 03:46:22');

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
(30, 2, 'TRX-1738900307-583', '12000.00', 'setoran', '2025-02-07 10:51:00', 'bayar kas', '2025-02-07 03:52:02', '2025-02-07 03:52:02'),
(37, 1, 'TRX-1739154365', '30000.00', 'setoran', '2025-02-10 09:26:00', 'bayar kas', '2025-02-10 02:26:19', '2025-02-10 02:26:19'),
(38, 2, 'TRX-1739255717', '10000.00', 'setoran', '2025-02-11 13:35:00', 'bayar kas bulan Februari', '2025-02-11 06:35:38', '2025-02-11 06:35:38'),
(39, 1, 'TRX-1739852783', '30000.00', 'setoran', '2025-02-18 11:26:00', 'bayar kas', '2025-02-18 04:26:40', '2025-02-18 04:26:40'),
(40, 3, 'TRX-1740368769', '30000.00', 'setoran', '2025-02-24 10:46:00', 'jhhjhkhk', '2025-02-24 03:46:22', '2025-02-24 03:46:22');

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
  `kelas_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `kelas_id`, `created_at`, `updated_at`) VALUES
(1, 'Bambang', 'hjhj@gmail.com', '$2y$10$pSnEYl3v3QPpamm0KSX96ufpOdsr1bqI1TgtPUT9XCtdvHDGEzha2', 'admin', 1, '2025-01-24 03:37:50', '2025-02-24 06:01:46'),
(3, 'putri', 'putri@gmail.com', '$2y$10$lljHCm4GGDjcVvzb5geaW.qiwuhbH3M8Djz5IuWPbNezkxphyby0G', 'bendahara', 1, '2025-01-24 06:02:19', '2025-02-24 06:05:55'),
(4, 'fahri', 'al@gmail.com', '$2y$10$OJiHuH98RidZGZnSiNe9y.g353s/tF4DyWSSLiu8R2wjmBtAmiquq', 'siswa', NULL, '2025-01-24 11:30:10', '2025-01-27 10:04:15'),
(5, 'putra', 'karim@gmail.com', '$2y$10$X8DkahKrqwqlwKoLuXmvP.gEoGIpB.9TwY4XjG2dLzcIT4adlpwlu', 'siswa', NULL, '2025-02-07 01:37:21', '2025-02-11 06:58:51'),
(6, 'kurniawan', 'kurniawan@gmail.com', '$2y$10$QVTuXIKtjshxY5d/cY3ZJepmjSLa6PhCa9sj0fGQcCSGerzE/IgbW', 'admin', 2, '2025-02-24 03:35:11', '2025-02-24 06:08:09');

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
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_kelas` (`kelas_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `penarikan`
--
ALTER TABLE `penarikan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
