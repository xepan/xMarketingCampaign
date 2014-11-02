-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 02, 2014 at 02:15 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `xepan-marketing`
--

-- --------------------------------------------------------

--
-- Table structure for table `xmarketingcampaign_campaignnewsletter`
--

CREATE TABLE IF NOT EXISTS `xmarketingcampaign_campaignnewsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epan_id` int(11) DEFAULT NULL,
  `newsletter_id` int(11) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `post_to_socials` tinyint(1) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_epan_id` (`epan_id`),
  KEY `fk_newsletter_id` (`newsletter_id`),
  KEY `fk_campaign_id` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xmarketingcampaign_campaigns`
--

CREATE TABLE IF NOT EXISTS `xmarketingcampaign_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epan_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `starting_date` datetime DEFAULT NULL,
  `ending_date` datetime DEFAULT NULL,
  `effective_start_date` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_epan_id` (`epan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xmarketingcampaign_config`
--

CREATE TABLE IF NOT EXISTS `xmarketingcampaign_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epan_id` int(11) DEFAULT NULL,
  `email_host` varchar(255) DEFAULT NULL,
  `email_port` varchar(255) DEFAULT NULL,
  `email_username` varchar(255) DEFAULT NULL,
  `email_password` varchar(255) DEFAULT NULL,
  `email_reply_to` varchar(255) DEFAULT NULL,
  `email_reply_to_name` varchar(255) DEFAULT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `email_threshold` int(11) DEFAULT NULL,
  `smtp_auto_reconnect` int(11) DEFAULT NULL,
  `use_for_domains` varchar(255) DEFAULT NULL,
  `email_transport` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `encryption` varchar(255) DEFAULT NULL,
  `emails_in_BCC` int(11) DEFAULT NULL,
  `last_engaged_at` datetime DEFAULT NULL,
  `email_sent_in_this_minute` int(11) DEFAULT NULL,
  `return_path` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_epan_id` (`epan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xmarketingcampaign_cp_sub_cat`
--

CREATE TABLE IF NOT EXISTS `xmarketingcampaign_cp_sub_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epan_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_associate` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_epan_id` (`epan_id`),
  KEY `fk_campaign_id` (`campaign_id`),
  KEY `fk_category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `xMarketingCampaign_data_grabber`
--

CREATE TABLE IF NOT EXISTS `xMarketingCampaign_data_grabber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epan_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `site_url` varchar(255) DEFAULT NULL,
  `query_parameter` varchar(255) DEFAULT NULL,
  `paginator_parameter` varchar(255) DEFAULT NULL,
  `paginator_initial_value` varchar(255) DEFAULT NULL,
  `records_per_page` varchar(255) DEFAULT NULL,
  `paginator_based_on` varchar(255) DEFAULT NULL,
  `extra_url_parameters` varchar(255) DEFAULT NULL,
  `required_pause_between_hits` varchar(255) DEFAULT NULL,
  `result_selector` varchar(255) DEFAULT NULL,
  `result_format` varchar(255) DEFAULT NULL,
  `json_url_key` varchar(255) DEFAULT NULL,
  `reg_ex_on_href` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_run_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_epan_id` (`epan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `xMarketingCampaign_data_grabber`
--

INSERT INTO `xMarketingCampaign_data_grabber` (`id`, `epan_id`, `name`, `site_url`, `query_parameter`, `paginator_parameter`, `paginator_initial_value`, `records_per_page`, `paginator_based_on`, `extra_url_parameters`, `required_pause_between_hits`, `result_selector`, `result_format`, `json_url_key`, `reg_ex_on_href`, `created_at`, `last_run_at`, `is_active`) VALUES
(1, 1, 'http://www.bing.com', 'http://www.bing.com', 'q', 'first', '1', '10', 'records', '', '30', 'li.b_algo', 'HTML', '', '', '2014-10-25 09:55:21', '2014-10-25 09:55:21', 1),
(2, 1, 'Google Ajax (Limited to max 64 records)', 'http://ajax.googleapis.com/ajax/services/search/web', 'q', 'start', '0', '8', 'records', 'v=1.0&rsz=8', '10', '', 'JSON', 'unescapedUrl', '', '2014-10-25 09:56:51', '2014-10-25 09:56:51', 1),
(3, 1, 'Google.com', 'http://www.google.co.in/search', 'q', 'start', '0', '10', 'records', '', '10', '#ires li.g h3', 'HTML', '', '^\\/url\\?q=(.*)&sa=(.*)$', '2014-10-25 09:57:58', '2014-10-31 20:34:24', 1);

-- --------------------------------------------------------

--
-- Table structure for table `xMarketingCampaign_data_search_phrase`
--

CREATE TABLE IF NOT EXISTS `xMarketingCampaign_data_search_phrase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_grabber_id` int(11) DEFAULT NULL,
  `subscription_category_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content_provided` longtext,
  `max_record_visit` varchar(255) DEFAULT NULL,
  `max_domain_depth` varchar(255) DEFAULT NULL,
  `max_page_depth` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `page_parameter_start_value` varchar(255) DEFAULT NULL,
  `page_parameter_max_value` varchar(255) DEFAULT NULL,
  `last_page_checked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_data_grabber_id` (`data_grabber_id`),
  KEY `fk_subscription_category_id` (`subscription_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
