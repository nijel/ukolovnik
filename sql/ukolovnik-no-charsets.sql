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
  `id` int(11) NOT NULL,
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
  `id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `created` timestamp NOT NULL,
  `updated` timestamp NULL default NULL,
  `closed` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;
