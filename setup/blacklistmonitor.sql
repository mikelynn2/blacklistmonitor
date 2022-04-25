-- CREATE DATABASE IF NOT EXISTS `blacklistmonitor` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
-- USE `blacklistmonitor`;


DROP TABLE IF EXISTS `blockLists`;
CREATE TABLE IF NOT EXISTS `blockLists` (
  `host` varchar(100) NOT NULL,
  `monitorType` enum('ip','domain') NOT NULL,
  `functionCall` enum('rbl') NOT NULL DEFAULT 'rbl',
  `description` varchar(400) NOT NULL,
  `website` varchar(500) NOT NULL,
  `lastBlockReport` datetime NOT NULL,
  `importance` enum('1','2','3') NOT NULL DEFAULT '2',
  `isActive` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0=inactive;1=active',
  `blocksToday` int(11) NOT NULL DEFAULT '0',
  `blocksYesterday` int(11) NOT NULL DEFAULT '0',
  `cleanToday` int(11) NOT NULL DEFAULT '0',
  `cleanYesterday` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blockLists`
--

INSERT INTO `blockLists` (`host`, `monitorType`, `functionCall`, `description`, `website`, `lastBlockReport`, `importance`, `isActive`, `blocksToday`, `blocksYesterday`, `cleanToday`, `cleanYesterday`) VALUES
('0spam.fusionzero.com', 'ip', 'rbl', 'Hosts sending to spam traps', 'http://0spam.fusionzero.com', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('0spamurl.fusionzero.com', 'ip', 'rbl', 'IPs found in links', 'http://0spam.fusionzero.com/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('b.barracudacentral.org', 'ip', 'rbl', 'Barracuda spam filtering.  One of the quicker reacting lists for spam and virus traffic coming from hosts.', 'http://www.barracudacentral.org', '0000-00-00 00:00:00', '3', '0', 0, 0, 0, 0),
('bl.blocklist.de', 'ip', 'rbl', 'Fail2ban reporting.  SSH, mail, ftp, http, etc attacks.', 'http://www.blocklist.de/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('bl.mailspike.net', 'ip', 'rbl', 'Uses seed system to collect stats about IPs.', 'http://www.mailspike.net/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('bl.spamcannibal.org', 'ip', 'rbl', 'Seed system and complaint mails forwarded to them.', 'http://www.spamcannibal.org', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('bl.spamcop.net', 'ip', 'rbl', 'Automated listings from user submitted spam reports.', 'http://www.spamcop.net/', '0000-00-00 00:00:00', '3', '0', 0, 0, 0, 0),
('bl.spameatingmonkey.net', 'ip', 'rbl', 'Listing entered by list reported by trusted users and IPs sending mail to a spamtraps.  IPs from spam traps are automatically expired after 7 days of inactivity.', 'http://spameatingmonkey.com/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('bl.tiopan.com', 'ip', 'rbl', 'Unknown - proprietary', 'http://www.tiopan.com/blacklist.php', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('blackholes.five-ten-sg.com', 'ip', 'rbl', 'List has shut down.', 'http://www.five-ten-sg.com/blackhole.php', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('blackholes.intersil.net', 'ip', 'rbl', 'List has shut down.', 'https://www.google.com/search?q=blackholes.intersil.net', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('bogons.cymru.com', 'ip', 'rbl', 'Filters IPs that aren''t allocated by ARIN.  Legitimately acquired ips will never show up on this list.  There''s no point of monitoring it if you have valid IPs.', 'https://www.team-cymru.org/Services/Bogons/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('cbl.abuseat.org', 'ip', 'rbl', 'Spam traps and administrator listings.', 'http://cbl.abuseat.org/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('cbl.anti-spam.org.cn', 'ip', 'rbl', 'Chinese Anti-Spam Alliance blacklist service', 'http://www.anti-spam.org.cn/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('combined.njabl.org', 'ip', 'rbl', 'List has shut down.', 'https://www.google.com/search?q=combined.njabl.org', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('db.wpbl.info', 'ip', 'rbl', 'Fully automated.  IPs automatically removed after a time.', 'http://wpbl.info/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dbl.spamhaus.org', 'domain', 'rbl', 'This mostly automated blacklist is usually activated by mailing invalid seed email addresses that are operated by spamhaus.  Your domain will usually be automatically removed from this list unless you continue to email their spam traps.  Then they will escalate the issue to larger blocks.', 'http://www.spamhaus.org/dbl/', '0000-00-00 00:00:00', '3', '1', 0, 0, 0, 0),
('dbl.tiopan.com', 'domain', 'rbl', 'Unknown - proprietary', 'http://www.tiopan.com/blacklist.php', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl-1.uceprotect.net', 'ip', 'rbl', 'Spamtraps and trusted sources.  Only lists single ips.', 'http://www.uceprotect.net/en/index.php', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl-2.uceprotect.net', 'ip', 'rbl', 'Spamtraps and trusted sources.  Will list large blocks of IPs that are connected.', 'http://www.uceprotect.net/en/index.php', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl-3.uceprotect.net', 'ip', 'rbl', 'Spamtraps and trusted sources.  Will list entire ASN''s.', 'http://www.uceprotect.net/en/index.php', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl.ahbl.org', 'ip', 'rbl', 'List of IP that spammers own and use in their UCE/UBE/spam.  They have several ways of identifying the domains manual and automated.', 'http://ahbl.org/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl.inps.de', 'ip', 'rbl', 'IP has sent email to seed email addresses.', 'http://dnsbl.inps.de/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl.justspam.org', 'ip', 'rbl', 'Claims to list only the worst offenders.', 'http://www.justspam.org/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('dnsbl.sorbs.net', 'ip', 'rbl', 'Sorbs composite blocklist.  Specializes in real time proxy and open relay checks.', 'http://www.sorbs.net/', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('dnsblchile.org', 'ip', 'rbl', 'RBL run from Chile.  If you have a large user base from Chile it may benefit you to monitor this list.  Otherwise probably not.', 'http://www.dnsblchile.org/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('dyna.spamrats.com', 'ip', 'rbl', 'RATS-Dyna is a collection of IP Addresses that have been found sending an abusive amount of connections, or trying too many invalid users at ISP and Telco''s mail servers, and are also known to conform to a naming convention that is indicative of a home connection or dynamic address space.', 'http://spamrats.com/rats-dyna.php', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('ip.v4bl.org', 'ip', 'rbl', 'They do not allow commercial use.  Unfortunately you''ll have to check this one manually.', 'http://v4bl.org/', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('ips.backscatterer.org', 'ip', 'rbl', 'IPs that send misdirected bounces or autoresponders.  Listing for 4 weeks.', 'http://www.backscatterer.org/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('ix.dnsbl.manitu.net', 'ip', 'rbl', 'IPs that send to their seed address system.', 'http://www.dnsbl.manitu.net/', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('l1.apews.org', 'domain', 'rbl', 'Apews automated system attempts to list domains of spammers before they start.', 'http://apews.org', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('l2.apews.org', 'ip', 'rbl', 'Apews automated system attempts to list ips of spammers before they start.', 'http://www.apews.org/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('list.anonwhois.net', 'domain', 'rbl', 'Checks for private registered domains. Private registration tends to count against delivery ability.  There''s no point in checking if your own domains are privately registered.  So this one has been disabled.', 'http://anonwhois.org/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('multi.surbl.org', 'domain', 'rbl', 'Combined block list of domains known to be used in either spamming, phishing, or malware.', 'http://www.surbl.org/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('multi.uribl.com', 'domain', 'rbl', 'Lists domains in emails.', 'http://www.uribl.com/index.shtml', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('no-more-funn.moensted.dk', 'ip', 'rbl', 'List has shut down.', 'http://moensted.dk/spam/no-more-funn/', '0000-00-00 00:00:00', '1', '1', 1, 0, 0, 0),
('noptr.spamrats.com', 'ip', 'rbl', 'RATS-NoPtr is a collection of IP Addresses that have been found sending an abusive amount of connections, or trying too many invalid users at ISP and Telco''s mail servers, and are also known to have no reverse DNS, a technique often used by bots and spammers. Email servers should always have reverse DNS entries.', 'http://spamrats.com/rats-noptr.php', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('psbl.surriel.com', 'ip', 'rbl', 'IPs pulled from their spam trap network.  Fully automated.', 'http://psbl.org/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('rbl.efnetrbl.org', 'ip', 'rbl', 'TOR nodes, open proxies, and spamtraps.', 'http://rbl.efnetrbl.org/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('recent.spam.dnsbl.sorbs.net', 'ip', 'rbl', 'Sorbs recent list.  IPs reported in the last 28 days.', 'http://www.sorbs.net/general/using.shtml', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('spam.abuse.ch', 'ip', 'rbl', 'IPs that send to spamtraps.', 'http://www.abuse.ch/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('spam.dnsbl.anonmails.de', 'ip', 'rbl', 'An anonymous mailbox provider.  They don''t provide many details about their block list.', 'http://www.anonmails.de/dnsbl.php', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('spam.dnsbl.sorbs.net', 'ip', 'rbl', 'Listings for hosts that aren''t taking actions.', 'http://www.sorbs.net/general/using.shtml', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('spam.spamrats.com', 'ip', 'rbl', 'This is a list of IP Addresses that do not conform to more commonly known threats, and is usually because of compromised servers, hosts, or open relays. However, since there is little accompanying data this list COULD have false-positives, and we suggest that it only is used if you support a more aggressive stance.', 'http://spamrats.com/rats-spam.php', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('spamguard.leadmon.net', 'ip', 'rbl', 'Small personally run RBL.  Not used by any known ISPs.', 'http://www.leadmon.net/SpamGuard/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('spamsources.fabel.dk', 'ip', 'rbl', 'Not much known.  They only state that they will list your network when if send them spam.', 'http://www.spamsources.fabel.dk/', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('t1.dnsbl.net.au', 'ip', 'rbl', 'Appears to be shutdown.', 'http://dnsbl.net.au/', '0000-00-00 00:00:00', '1', '0', 0, 0, 0, 0),
('tor.dnsbl.sectoor.de', 'ip', 'rbl', '', '', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('torexit.dan.me.uk', 'ip', 'rbl', 'Contains all exit nodes on the TOR network.', 'https://www.dan.me.uk/dnsbl', '0000-00-00 00:00:00', '1', '1', 0, 0, 0, 0),
('truncate.gbudb.net', 'ip', 'rbl', 'Problem hosts as seen by Message Sniffer application', 'http://www.gbudb.com/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('ubl.unsubscore.com', 'ip', 'rbl', 'Lashback''s monitoring for senders failing to provide a working or not honoring an unsubscribe mechanism.', 'http://blacklist.lashback.com/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('urired.spameatingmonkey.net', 'domain', 'rbl', 'Domains found in unwanted emails and preemptive technology. Automatically expires after 30 days of inactivity.', 'http://spameatingmonkey.com/', '0000-00-00 00:00:00', '2', '1', 0, 0, 0, 0),
('virbl.dnsbl.bit.nl', 'ip', 'rbl', '', '', '0000-00-00 00:00:00', '2', '0', 0, 0, 0, 0),
('zen.spamhaus.org', 'ip', 'rbl', 'Composite host block list of all spamhaus ips.', 'http://www.spamhaus.org/zen/', '0000-00-00 00:00:00', '3', '1', 0, 0, 0, 0),
('rbldns.weburl.ro', 'ip', 'rbl', 'rbl.claus.ro', 'https://rbl.claus.ro/login.php', '0000-00-00 00:00:00', '3', '1', '0', '0', '0', '0');
--
-- Table structure for table `monitorHistory`
--

DROP TABLE IF EXISTS `monitorHistory`;
CREATE TABLE IF NOT EXISTS `monitorHistory` (
  `monitorTime` datetime NOT NULL,
  `isBlocked` tinyint(1) NOT NULL DEFAULT '0',
  `ipDomain` varchar(100) NOT NULL,
  `rDNS` varchar(200) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `monitors`
--

DROP TABLE IF EXISTS `monitors`;
CREATE TABLE IF NOT EXISTS `monitors` (
  `ipDomain` varchar(100) NOT NULL,
  `isDomain` tinyint(1) NOT NULL DEFAULT '0',
  `beenChecked` tinyint(1) NOT NULL DEFAULT '0',
  `lastUpdate` datetime NOT NULL,
  `isBlocked` tinyint(1) NOT NULL DEFAULT '0',
  `lastStatusChanged` tinyint(1) NOT NULL DEFAULT '0',
  `monitorGroupId` int(11) NOT NULL DEFAULT '0',
  `keepOnUpdate` tinyint(1) NOT NULL DEFAULT '1',
  `lastStatusChangeTime` datetime NOT NULL,
  `rDNS` varchar(200) NOT NULL,
  `status` text NOT NULL,
  `isActive` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0=inactive;1=active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(100) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  `apiKey` varchar(32) NOT NULL,
  `lastUpdate` datetime NOT NULL,
  `lastChecked` datetime NOT NULL,
  `beenChecked` tinyint(1) NOT NULL DEFAULT '0',
  `lastRunTime` int(11) NOT NULL DEFAULT '0',
  `disableEmailNotices` tinyint(1) NOT NULL DEFAULT '0',
  `twitterHandle` varchar(15) NOT NULL,
  `checkFrequency` enum('1hour','2hour','8hour','daily','weekly') NOT NULL DEFAULT 'daily',
  `noticeEmailAddresses` varchar(8000) NOT NULL,
  `textMessageEmails` varchar(8000) NOT NULL,
  `apiCallbackURL` varchar(2000) NOT NULL,
  `ips` LONGTEXT NOT NULL,
  `domains` LONGTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `users` (`username`, `passwd`, `apiKey`, `lastUpdate`, `lastChecked`, `beenChecked`, `lastRunTime`, `disableEmailNotices`, `twitterHandle`, `checkFrequency`, `noticeEmailAddresses`, `textMessageEmails`, `apiCallbackURL`, `ips`, `domains`) VALUES
('admin', '97bf34d31a8710e6b1649fd33357f783', '', '2015-06-02 16:37:02', '2015-06-02 16:22:55', 1, 4, 0, '', '2hour', '', '', '', '', '');


DROP TABLE IF EXISTS `monitorGroup`;
CREATE TABLE IF NOT EXISTS `monitorGroup` (
  `id` int(11) NOT NULL,
  `groupName` varchar(100) NOT NULL,
  `ips` longtext NOT NULL,
  `domains` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `blockLists`
--
ALTER TABLE `blockLists`
  ADD PRIMARY KEY (`host`),
  ADD KEY `monitorType` (`monitorType`),
  ADD KEY `isActive` (`isActive`);

--
-- Indexes for table `monitorHistory`
--
ALTER TABLE `monitorHistory`
  ADD KEY `monitorTime` (`monitorTime`),
  ADD KEY `ipDomain` (`ipDomain`);

--
-- Indexes for table `monitors`
--
ALTER TABLE `monitors`
  ADD PRIMARY KEY (`ipDomain`),
  ADD KEY `beenChecked` (`beenChecked`),
  ADD KEY `isBlocked` (`isBlocked`),
  ADD KEY `lastUpdate` (`lastUpdate`),
  ADD KEY `isDomain` (`isDomain`),
  ADD KEY `monitorGroupId` (`monitorGroupId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `monitorGroup`
--
ALTER TABLE `monitorGroup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupName` (`groupName`);

ALTER TABLE `monitorGroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
