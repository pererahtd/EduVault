-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Nov 19, 2026 at 01:23 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_43384415_eduvault`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `publish_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_date`, `event_time`, `description`, `created_at`) VALUES
(5, 'computing workshop', '2026-11-17', '08:30:00', 'An industry-focused workshop designed to empower future software engineers with practical quality assurance practices', '2026-11-09 16:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'discussion',
  `title` varchar(160) NOT NULL,
  `author` varchar(100) NOT NULL,
  `owner_key` varchar(100) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `answer` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `type`, `title`, `author`, `owner_key`, `category`, `description`, `answer`, `photo`, `status`, `created_at`, `edited_at`) VALUES
(6, 'discussion', 'Cricket', 'Dilshan Dhananjaya', 'u-10', 'Batting', 'How to bat', '', NULL, 'Active', '2026-11-10 06:52:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `forum_replies`
--

CREATE TABLE `forum_replies` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `owner_key` varchar(100) DEFAULT NULL,
  `body` text NOT NULL,
  `best` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_reply_likes`
--

CREATE TABLE `forum_reply_likes` (
  `reply_id` int(11) NOT NULL,
  `owner_key` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#00a859',
  `ownerId` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `color`, `ownerId`, `created_at`) VALUES
(6, 'Kasunge group eka', '', '#ec4899', 10, '2026-11-09 22:48:21');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`groupId`, `userId`, `joined_at`) VALUES
(6, 10, '2026-11-09 22:48:21');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `group_id`, `user_id`, `message`, `created_at`) VALUES
(8, 6, 10, 'Hi', '2026-11-09 22:48:29'),
(9, 6, 10, '📅 New study session: Gym session — Tue 10 Nov, 1:00 PM', '2026-11-09 22:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category` varchar(80) NOT NULL DEFAULT 'General',
  `expires_at` date DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_invites`
--

CREATE TABLE `session_invites` (
  `id` int(11) NOT NULL,
  `sessionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_rsvps`
--

CREATE TABLE `session_rsvps` (
  `id` int(11) NOT NULL,
  `sessionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'going',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_rsvps`
--

INSERT INTO `session_rsvps` (`id`, `sessionId`, `userId`, `status`, `created_at`) VALUES
(2, 2, 10, 'going', '2026-11-09 22:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `study_groups`
--

CREATE TABLE `study_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `module` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_groups`
--

INSERT INTO `study_groups` (`id`, `name`, `module`, `description`, `created_at`) VALUES
(1, 'Sample Study Group', 'Database Management', 'Past paper practice', '2026-10-27 21:39:01'),
(2, 'AI Revision Circle', 'AI', 'Weekly revision of lecture notes', '2026-10-27 21:39:01'),
(3, 'Sample Study Group', 'Database Management', 'Past paper practice', '2026-10-28 23:55:04'),
(4, 'AI Revision Circle', 'AI', 'Weekly revision of lecture notes', '2026-10-28 23:55:04'),
(5, 'Sample Study Group', 'Database Management', 'Past paper practice', '2026-11-02 11:19:06'),
(6, 'AI Revision Circle', 'AI', 'Weekly revision of lecture notes', '2026-11-02 11:19:06');

-- --------------------------------------------------------

--
-- Table structure for table `study_materials`
--

CREATE TABLE `study_materials` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `batch` varchar(20) NOT NULL,
  `module` varchar(120) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_sessions`
--

CREATE TABLE `study_sessions` (
  `id` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `seriesId` int(11) DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `startsAt` datetime NOT NULL,
  `endsAt` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'scheduled',
  `visibility` varchar(10) NOT NULL DEFAULT 'group',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_sessions`
--

INSERT INTO `study_sessions` (`id`, `groupId`, `seriesId`, `createdBy`, `title`, `description`, `location`, `startsAt`, `endsAt`, `status`, `visibility`, `created_at`) VALUES
(2, 6, NULL, 10, 'Gym session', 'All are welcome', 'Gym', '2026-11-10 13:00:00', '2026-11-10 14:00:00', 'scheduled', 'group', '2026-11-09 22:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `created_at` datetime DEFAULT current_timestamp(),
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `lockout_time` datetime DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `login_attempts`, `lockout_time`, `profile_picture`) VALUES
(1, 'Thimira Perera', 'thimirad593@gmail.com', '$2y$12$j862W4ailCERlEukyIpXie4ZNa0EcV99rWaFJ7YSYnz8ATKQrNkP6', 'admin', '2026-11-05 22:43:49', 0, NULL, 'uploads/profiles/profile_8_1794218439.jpg'),
(5, 'Savidu Sathsara', 'sathsarasavindu397@gmail.com', '$2y$10$bKBBBTvm.JhC7I4YP0fDVO68oK8yBMD9MMr8WsCbfKOq4DQ5p/sp.', 'student', '2026-11-07 20:07:39', 0, NULL, 'uploads/profiles/profile_5_1794766047.jpg'),
(6, 'Savidu Sathsara', 'dlsssandeepa@students.nsbm.ac.lk', '$2y$10$wVaFUWXzWAd5icYFSRQXn.ftMOhVFJfvq1lbYIXpCWKp.6Ry3PZhm', 'admin', '2026-11-08 00:54:34', 0, NULL, 'uploads/profiles/profile_6_1794232590.jpg'),
(7, 'Umesh Yasiru', 'rmumeshyasiru2002@gmail.com', '$2y$12$YElsEsoMyRMy2latQEyM4u.CS/dcprbfiH0jJcAxSQAl/1Z.YbtI.', 'Admin', '2026-11-07 22:23:19', 0, NULL, NULL),
(9, 'Amalya Ganegoda', 'a.pooji06@gmail.com', '$2y$12$hcmWiGZrDDGVyonty4RBk.EuGfQy1rDwxqwmALRXCwHINH2FQ5cHG', 'Admin', '2026-11-08 06:22:31', 0, NULL, NULL),
(10, 'Dilshan Dhananjaya', 'dilshandana13@gmail.com', '$2y$12$q3mpq5hUz/apLI4Yv7imwesCTBJJlym1AT/l5gysC1U/rJzfRuvp2', 'student', '2026-11-08 09:38:00', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_replies`
--
ALTER TABLE `forum_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post` (`post_id`);

--
-- Indexes for table `forum_reply_likes`
--
ALTER TABLE `forum_reply_likes`
  ADD PRIMARY KEY (`reply_id`,`owner_key`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ownerId` (`ownerId`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`groupId`,`userId`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_invites`
--
ALTER TABLE `session_invites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_session_invite` (`sessionId`,`userId`);

--
-- Indexes for table `session_rsvps`
--
ALTER TABLE `session_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_session_user` (`sessionId`,`userId`);

--
-- Indexes for table `study_groups`
--
ALTER TABLE `study_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_sessions`
--
ALTER TABLE `study_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupId` (`groupId`),
  ADD KEY `seriesId` (`seriesId`),
  ADD KEY `startsAt` (`startsAt`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `forum_replies`
--
ALTER TABLE `forum_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `session_invites`
--
ALTER TABLE `session_invites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_rsvps`
--
ALTER TABLE `session_rsvps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `study_groups`
--
ALTER TABLE `study_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `study_materials`
--
ALTER TABLE `study_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `study_sessions`
--
ALTER TABLE `study_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
