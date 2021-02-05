--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` tinytext NOT NULL,
  `password` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `torque-eml` tinytext NOT NULL,
  `torque-id` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);
COMMIT;