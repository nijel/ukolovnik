-- phpMyAdmin SQL Dump
-- version 2.7.1-dev
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Dec 13, 2005 at 11:25 PM
-- Server version: 5.0.16
-- PHP Version: 5.0.5-3
-- 
-- Database: `ukolovnik`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `categories`
-- 

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `personal` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `personal` (`personal`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks`
-- 

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `title` varchar(200) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated` timestamp NULL default NULL,
  `closed` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
