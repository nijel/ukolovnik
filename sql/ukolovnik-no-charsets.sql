-- phpMyAdmin SQL Dump
-- version 2.7.1-dev
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 14, 2006 at 01:49 PM
-- Server version: 5.0.18
-- PHP Version: 5.1.1-1
-- 
-- Database: `ukolovnik`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `ukolovnik_categories`
-- 

DROP TABLE IF EXISTS `ukolovnik_categories`;
CREATE TABLE `ukolovnik_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `personal` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `personal` (`personal`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `ukolovnik_tasks`
-- 

DROP TABLE IF EXISTS `ukolovnik_tasks`;
CREATE TABLE `ukolovnik_tasks` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `created` timestamp NOT NULL,
  `updated` timestamp NULL default NULL,
  `closed` timestamp NULL default NULL,
  `update_count` bigint default 0,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `ukolovnik_settings`
-- 

DROP TABLE IF EXISTS `ukolovnik_settings`;
CREATE TABLE `ukolovnik_settings` (
  `key` varchar(200) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`key`)
) TYPE=MyISAM;

INSERT INTO `ukolovnik_settings` SET `key`="version", `value`=2;
