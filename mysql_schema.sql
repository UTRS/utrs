SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `actionAppealLog` (
  `commentID` int(11),
  `appealID` int(11),
  `timestamp` timestamp,
  `comment` varchar(10000),
  `commentUser` int(11),
  `action` tinyint(1)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appeal` (
  `appealID` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(32) DEFAULT NULL,
  `wikiAccountName` varchar(255) DEFAULT NULL,
  `autoblock` tinyint(1) NOT NULL,
  `hasAccount` tinyint(1) NOT NULL,
  `blockingAdmin` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `appealText` varchar(4096) NOT NULL,
  `intendedEdits` varchar(1024) NOT NULL,
  `otherInfo` varchar(1024) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `handlingAdmin` int(11) DEFAULT NULL,
  `oldHandlingAdmin` int(11) DEFAULT NULL,
  `lastLogId` int(11) DEFAULT NULL,
  `emailToken` char(64) DEFAULT NULL,
  PRIMARY KEY (`appealID`),
  KEY `handlingAdmin` (`handlingAdmin`),
  FULLTEXT KEY `email` (`email`,`wikiAccountName`,`blockingAdmin`,`appealText`,`intendedEdits`,`otherInfo`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banList` (
  `banID` int(11) NOT NULL AUTO_INCREMENT,
  `target` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry` timestamp NULL DEFAULT NULL,
  `reason` varchar(1024) NOT NULL,
  `admin` int(11) NOT NULL,
  `isIP` tinyint(1) NOT NULL,
  PRIMARY KEY (`banID`),
  UNIQUE KEY `target` (`target`),
  KEY `admin` (`admin`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `commentID` int(11) NOT NULL AUTO_INCREMENT,
  `appealID` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(10000) NOT NULL,
  `commentUser` int(11) DEFAULT NULL,
  `action` tinyint(1) NOT NULL DEFAULT '0',
  `reported` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`commentID`),
  KEY `appealID` (`appealID`),
  KEY `commentUser` (`commentUser`),
  FULLTEXT KEY `comment` (`comment`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuData` (
  `appealID` int(11) NOT NULL,
  `useragent` varchar(1024) NOT NULL,
  PRIMARY KEY (`appealID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `irc` (
  `ircID` int(11) NOT NULL AUTO_INCREMENT,
  `notification` varchar(256) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unblock` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ircID`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loggedInUsers` (
  `userID` int(11) NOT NULL,
  `lastPageView` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sitenotice` (
  `messageID` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(2048) NOT NULL,
  `author` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`messageID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `templateID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `text` varchar(4096) NOT NULL,
  `lastEditTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastEditUser` int(11) NOT NULL,
  `statusUser` tinyint(1) DEFAULT NULL,
  `statusClose` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`templateID`),
  UNIQUE KEY `name` (`name`),
  KEY `lastEditUserIdx` (`lastEditUser`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `username` varchar(255) NOT NULL,
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `wikiAccount` varchar(255) NOT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `toolAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `checkuser` tinyint(1) NOT NULL DEFAULT '0',
  `developer` tinyint(1) NOT NULL DEFAULT '0',
  `passwordHash` varchar(255) NOT NULL,
  `useSecure` tinyint(1) NOT NULL DEFAULT '1',
  `replyNotify` tinyint(1) DEFAULT '1',
  `comments` text,
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed` tinyint(11) NOT NULL DEFAULT '0',
  `resetConfirm` varchar(32) DEFAULT NULL,
  `resetTime` timestamp NULL DEFAULT NULL,
  `diff` varchar(512) NOT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `wikiAccount` (`wikiAccount`),
  KEY `approved` (`approved`),
  KEY `active` (`active`),
  KEY `toolAdmin` (`toolAdmin`),
  KEY `registered` (`registered`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userMgmtLog` (
  `logID` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `reason` varchar(1024) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `target` int(11) NOT NULL,
  `doneBy` int(11) NOT NULL,
  `hideTarget` tinyint(4) NOT NULL,
  PRIMARY KEY (`logID`),
  KEY `target` (`target`),
  KEY `doneBy` (`doneBy`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--HOOK TOOL

CREATE TABLE IF NOT EXISTS `hooks` (
  `user_id` int(11) NOT NULL,
  `hook_class` varchar(32) NOT NULL,
  `zone` int(11) NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `config` (
  `config` varchar(16) NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `config` (`config`, `data`) VALUES
('installed_hooks', 'a:12:{i:0;s:13:"AwaitingProxy";i:1;s:16:"AwaitingReviewer";i:2;s:17:"AwaitingToolAdmin";i:3;s:12:"AwaitingUser";i:4;s:7:"Backlog";i:5;s:15:"CheckUserNeeded";i:6;s:14:"ClosedRequests";i:7;s:7:"MyQueue";i:8;s:11:"NewRequests";i:9;s:6:"OnHold";i:10;s:17:"UnverifiedAppeals";i:11;s:11:"WaitingOnMe";}');

--End of hook tool


/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP TABLE IF EXISTS `actionAppealLog`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`unblock`@`%.toolserver.org` SQL SECURITY DEFINER */
/*!50001 VIEW `actionAppealLog` AS select `utrs_dev`.`comment`.`commentID` AS `commentID`,`utrs_dev`.`comment`.`appealID` AS `appealID`,`utrs_dev`.`comment`.`timestamp` AS `timestamp`,`utrs_dev`.`comment`.`comment` AS `comment`,`utrs_dev`.`comment`.`commentUser` AS `commentUser`,`utrs_dev`.`comment`.`action` AS `action` from `comment` where (`utrs_dev`.`comment`.`action` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;


