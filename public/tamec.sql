-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 25, 2026 at 10:07 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tamec`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middlename` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `residential_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Name of the residence or facility',
  `residential_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `residential_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `residential_province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `residential_postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `residential_country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Canada',
  `billing_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Name for billing purposes',
  `billing_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Canada',
  `billing_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email for billing purposes',
  `bill_rate` decimal(10,2) DEFAULT NULL COMMENT 'Client''s billing rate',
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'TRUE = Active, FALSE = Inactive',
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main table for storing client information';

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `firstname`, `middlename`, `lastname`, `mobile`, `email`, `residential_name`, `residential_address`, `residential_city`, `residential_province`, `residential_postal_code`, `residential_country`, `billing_name`, `billing_address`, `billing_city`, `billing_province`, `billing_postal_code`, `billing_country`, `billing_email`, `bill_rate`, `latitude`, `longitude`, `is_active`, `reg_date`, `updated_at`, `created_by`) VALUES
(1, 'Margaret', 'Anne', 'Thompson', '+1-416-555-1001', 'margaret.thompson@email.com', 'Thompson Residence', '123 Elder Care Lane', 'Toronto', 'Ontario', 'M5H 2N2', 'Canada', 'Margaret Thompson (Personal)', '123 Elder Care Lane', 'Toronto', 'Ontario', 'M5H 2N2', 'Canada', 'billing.thompson@email.com', NULL, '0.00000000', '0.00000000', 1, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(2, 'Robert', 'James', 'Anderson', '+1-905-555-1002', 'robert.anderson@email.com', 'Anderson Family Home', '456 Wellness Avenue', 'Mississauga', 'Ontario', 'L5B 4P8', 'Canada', 'Anderson Family Trust', '456 Wellness Avenue', 'Mississauga', 'Ontario', 'L5B 4P8', 'Canada', 'finance.anderson@email.com', '30.00', '0.00000000', '0.00000000', 1, '2026-03-13 13:38:48', '2026-03-18 14:56:27', NULL),
(3, 'Elizabeth', 'Grace', 'Wilson', '+1-647-555-1003', 'elizabeth.wilson@email.com', 'Wilson Senior Care', '789 Maple Street', 'Toronto', 'Ontario', 'M6G 1C3', 'Canada', 'Estate of Elizabeth Wilson', '789 Maple Street', 'Toronto', 'Ontario', 'M6G 1C3', 'Canada', 'estate.wilson@email.com', '70.00', '43.77203800', '-79.68755450', 1, '2026-03-13 13:38:48', '2026-03-18 14:54:21', NULL),
(4, 'William', 'Henry', 'Davis', '+1-416-555-1004', 'william.davis@email.com', 'Davis Residence', '321 Pinecrest Road', 'Toronto', 'Ontario', 'M4B 1E5', 'Canada', 'William Davis (Personal)', '321 Pinecrest Road', 'Toronto', 'Ontario', 'M4B 1E5', 'Canada', 'billing.davis@email.com', NULL, '0.00000000', '0.00000000', 1, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(5, 'Susan', 'Marie', 'Martinez', '+1-905-555-1005', 'susan.martinez@email.com', 'Martinez Household', '654 Oakwood Drive', 'Brampton', 'Ontario', 'L6Y 5P2', 'Canada', 'Martinez Family Care', '654 Oakwood Drive', 'Brampton', 'Ontario', 'L6Y 5P2', 'Canada', 'care.martinez@email.com', NULL, '0.00000000', '0.00000000', 1, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(6, 'Faleti', '', 'Abdul', '08082709997', 'hercease001@gmail.com', 'Faaz House', '5, Powerline Street Off Iloye Street', 'Ota', 'Ontario', '112212', 'Canada', 'Faaz House', '5,bode ojo Street oke baale osogbo', 'Osogbo', 'Ontario', '+234', 'Canada', 'hercease001@gmail.com', '30.00', '0.00000000', '0.00000000', 1, '2026-03-18 06:16:55', '2026-03-18 12:08:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `holiday_id` int NOT NULL,
  `holiday_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the holiday (e.g., Canada Day)',
  `fixed_month` tinyint DEFAULT NULL COMMENT 'Month for fixed-date holidays (1-12)',
  `fixed_day` tinyint DEFAULT NULL COMMENT 'Day for fixed-date holidays (1-31)',
  `premium_percentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Additional percentage to add to regular pay (e.g., 50 for time-and-a-half)',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`holiday_id`, `holiday_name`, `fixed_month`, `fixed_day`, `premium_percentage`, `is_active`, `created_at`, `updated_at`, `created_by`) VALUES
(3, 'New year', 1, 1, '50.00', 1, '2026-03-23 18:06:17', '2026-03-23 18:06:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int NOT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique invoice identifier (e.g., INV-2024-001)',
  `client_id` int NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_staff` int NOT NULL DEFAULT '0',
  `total_hours` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) DEFAULT '13.00' COMMENT 'Tax rate percentage',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `shipping` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','sent','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `po_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Purchase Order Number',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sent_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice records for client billing';

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `hours_worked` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `schedule_ids` json DEFAULT NULL COMMENT 'Array of schedule IDs included in this invoice item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Street address',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'City',
  `province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Province/State',
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Postal/ZIP code',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Canada' COMMENT 'Country',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude for mapping',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude for mapping',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'TRUE = Active, FALSE = Inactive',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the location was created',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'Staff ID who created this location'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `address`, `city`, `province`, `postal_code`, `country`, `latitude`, `longitude`, `is_active`, `created_on`, `updated_at`, `created_by`) VALUES
(1, '123 University Avenue', 'Toronto', 'Ontario', 'M5G 1Y6', 'Canada', '43.65470000', '-79.38860000', 1, '2026-03-15 17:49:13', '2026-03-15 17:49:13', NULL),
(2, '200 Elizabeth Street', 'Toronto', 'Ontario', 'M5G 2C4', 'Canada', '43.65730000', '-79.38830000', 1, '2026-03-15 17:49:13', '2026-03-15 17:49:13', NULL),
(3, '2075 Bayview Avenue', 'Toronto', 'Ontario', 'M4N 3M5', 'Canada', '43.72380000', '-79.37540000', 1, '2026-03-15 17:50:28', '2026-03-15 17:50:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `payroll_id` int NOT NULL,
  `payroll_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique payroll identifier (e.g., PR-2024-001)',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_staff` int NOT NULL DEFAULT '0',
  `total_hours` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','processed','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payroll records for staff payments';

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `item_id` int NOT NULL,
  `payroll_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `hours_worked` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `overtime_hours` decimal(10,2) DEFAULT '0.00',
  `overtime_rate` decimal(10,2) DEFAULT '0.00',
  `gross_pay` decimal(10,2) NOT NULL,
  `deductions` decimal(10,2) DEFAULT '0.00',
  `net_pay` decimal(10,2) NOT NULL,
  `schedule_ids` json DEFAULT NULL COMMENT 'Array of schedule IDs included in this payroll item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int NOT NULL,
  `permission_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique code like "staff.view" or "payroll.create"',
  `permission_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name',
  `permission_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of what this permission allows',
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'staff, clients, schedule, payroll, invoices, reports, settings, system',
  `submodule` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional submodule',
  `action_type` enum('view','create','edit','delete','approve','export','import','manage','configure') COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FontAwesome icon for UI',
  `display_order` int DEFAULT '0' COMMENT 'Order in UI',
  `depends_on` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Permission code this depends on (e.g., "staff.edit" depends on "staff.view")',
  `is_active` tinyint(1) DEFAULT '1',
  `is_system` tinyint(1) DEFAULT '0' COMMENT 'System permissions that cannot be deleted',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_code`, `permission_name`, `permission_description`, `module`, `submodule`, `action_type`, `icon_class`, `display_order`, `depends_on`, `is_active`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'dashboard.view', 'View Dashboard', 'Access the main dashboard', 'dashboard', NULL, 'view', 'fa-tachometer-alt', 1, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(2, 'dashboard.stats', 'View Statistics', 'View dashboard statistics and charts', 'dashboard', NULL, 'view', 'fa-chart-line', 2, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(3, 'staff.view', 'View Staff', 'View staff list and details', 'staff', NULL, 'view', 'fa-user-nurse', 10, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(4, 'staff.create', 'Create Staff', 'Add new staff members', 'staff', NULL, 'create', 'fa-user-plus', 11, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(5, 'staff.edit', 'Edit Staff', 'Edit existing staff information', 'staff', NULL, 'edit', 'fa-user-edit', 12, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(6, 'staff.delete', 'Delete Staff', 'Remove staff members', 'staff', NULL, 'delete', 'fa-user-minus', 13, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(7, 'staff.activate', 'Activate/Deactivate Staff', 'Change staff active status', 'staff', NULL, 'manage', 'fa-toggle-on', 14, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(8, 'staff.export', 'Export Staff', 'Export staff data to CSV/Excel', 'staff', NULL, 'export', 'fa-download', 15, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(9, 'staff.import', 'Import Staff', 'Bulk import staff data', 'staff', NULL, 'import', 'fa-upload', 16, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(10, 'staff.view_salary', 'View Staff Salary', 'View staff salary information', 'staff', 'salary', 'view', 'fa-money-bill', 17, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(11, 'staff.edit_salary', 'Edit Staff Salary', 'Modify staff salary details', 'staff', 'salary', 'edit', 'fa-money-bill-wave', 18, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(12, 'staff.view_documents', 'View Staff Documents', 'Access staff documents', 'staff', 'documents', 'view', 'fa-file-alt', 19, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(13, 'staff.upload_documents', 'Upload Staff Documents', 'Add documents to staff profiles', 'staff', 'documents', 'create', 'fa-file-upload', 20, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(14, 'clients.view', 'View Clients', 'View client list and details', 'clients', NULL, 'view', 'fa-user-tie', 30, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(15, 'clients.create', 'Create Clients', 'Add new clients', 'clients', NULL, 'create', 'fa-user-plus', 31, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(16, 'clients.edit', 'Edit Clients', 'Edit existing client information', 'clients', NULL, 'edit', 'fa-user-edit', 32, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(17, 'clients.delete', 'Delete Clients', 'Remove clients', 'clients', NULL, 'delete', 'fa-user-minus', 33, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(18, 'clients.activate', 'Activate/Deactivate Clients', 'Change client active status', 'clients', NULL, 'manage', 'fa-toggle-on', 34, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(19, 'clients.export', 'Export Clients', 'Export client data to CSV/Excel', 'clients', NULL, 'export', 'fa-download', 35, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(20, 'clients.import', 'Import Clients', 'Bulk import client data', 'clients', NULL, 'import', 'fa-upload', 36, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(21, 'clients.view_billing', 'View Billing Info', 'View client billing information', 'clients', 'billing', 'view', 'fa-file-invoice', 37, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(22, 'clients.edit_billing', 'Edit Billing Info', 'Modify client billing details', 'clients', 'billing', 'edit', 'fa-file-invoice-dollar', 38, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(23, 'schedule.view', 'View Schedule', 'View schedules and calendar', 'schedule', NULL, 'view', 'fa-calendar-alt', 50, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(24, 'schedule.create', 'Create Schedule', 'Create new schedules', 'schedule', NULL, 'create', 'fa-calendar-plus', 51, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(25, 'schedule.edit', 'Edit Schedule', 'Edit existing schedules', 'schedule', NULL, 'edit', 'fa-calendar-edit', 52, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(26, 'schedule.delete', 'Delete Schedule', 'Remove schedules', 'schedule', NULL, 'delete', 'fa-calendar-times', 53, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(27, 'schedule.approve', 'Approve Schedule', 'Approve pending schedules', 'schedule', NULL, 'approve', 'fa-check-circle', 54, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(28, 'schedule.export', 'Export Schedule', 'Export schedules to CSV/Excel', 'schedule', NULL, 'export', 'fa-download', 55, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(29, 'schedule.bulk_create', 'Bulk Create', 'Create multiple schedules at once', 'schedule', NULL, 'create', 'fa-calendar-plus', 56, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(30, 'schedule.view_all', 'View All Staff Schedules', 'View schedules for all staff', 'schedule', NULL, 'view', 'fa-users', 57, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(31, 'payroll.view', 'View Payroll', 'View payroll records', 'payroll', NULL, 'view', 'fa-money-bill-wave', 70, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(32, 'payroll.create', 'Create Payroll', 'Generate new payroll', 'payroll', NULL, 'create', 'fa-plus-circle', 71, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(33, 'payroll.edit', 'Edit Payroll', 'Edit existing payroll', 'payroll', NULL, 'edit', 'fa-edit', 72, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(34, 'payroll.delete', 'Delete Payroll', 'Delete payroll records', 'payroll', NULL, 'delete', 'fa-trash', 73, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(35, 'payroll.approve', 'Approve Payroll', 'Approve payroll for processing', 'payroll', NULL, 'approve', 'fa-check-circle', 74, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(36, 'payroll.process', 'Process Payroll', 'Mark payroll as processed', 'payroll', NULL, 'manage', 'fa-cog', 75, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(37, 'payroll.export', 'Export Payroll', 'Export payroll data', 'payroll', NULL, 'export', 'fa-download', 76, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(38, 'payroll.view_reports', 'View Payroll Reports', 'Access payroll reports', 'payroll', 'reports', 'view', 'fa-chart-pie', 77, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(39, 'invoices.view', 'View Invoices', 'View invoice list and details', 'invoices', NULL, 'view', 'fa-file-invoice', 90, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(40, 'invoices.create', 'Create Invoice', 'Generate new invoices', 'invoices', NULL, 'create', 'fa-plus-circle', 91, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(41, 'invoices.edit', 'Edit Invoice', 'Edit existing invoices', 'invoices', NULL, 'edit', 'fa-edit', 92, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(42, 'invoices.delete', 'Delete Invoice', 'Delete invoice records', 'invoices', NULL, 'delete', 'fa-trash', 93, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(43, 'invoices.send', 'Send Invoice', 'Send invoices to clients', 'invoices', NULL, 'manage', 'fa-paper-plane', 94, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(44, 'invoices.mark_paid', 'Mark as Paid', 'Mark invoices as paid', 'invoices', NULL, 'manage', 'fa-check-double', 95, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(45, 'invoices.export', 'Export Invoices', 'Export invoice data', 'invoices', NULL, 'export', 'fa-download', 96, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(46, 'invoices.view_reports', 'View Invoice Reports', 'Access invoice reports', 'invoices', 'reports', 'view', 'fa-chart-bar', 97, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(47, 'locations.view', 'View Locations', 'View location list', 'locations', NULL, 'view', 'fa-map-marker-alt', 110, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(48, 'locations.create', 'Create Location', 'Add new locations', 'locations', NULL, 'create', 'fa-plus-circle', 111, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(49, 'locations.edit', 'Edit Location', 'Edit existing locations', 'locations', NULL, 'edit', 'fa-edit', 112, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(50, 'locations.delete', 'Delete Location', 'Remove locations', 'locations', NULL, 'delete', 'fa-trash', 113, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(51, 'holidays.view', 'View Holidays', 'View holiday list', 'holidays', NULL, 'view', 'fa-sun', 120, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(52, 'holidays.create', 'Create Holiday', 'Add new holidays', 'holidays', NULL, 'create', 'fa-plus-circle', 121, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(53, 'holidays.edit', 'Edit Holiday', 'Edit existing holidays', 'holidays', NULL, 'edit', 'fa-edit', 122, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(54, 'holidays.delete', 'Delete Holiday', 'Remove holidays', 'holidays', NULL, 'delete', 'fa-trash', 123, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(55, 'reports.view', 'View Reports', 'Access reports section', 'reports', NULL, 'view', 'fa-chart-bar', 130, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(56, 'reports.generate', 'Generate Reports', 'Create new reports', 'reports', NULL, 'create', 'fa-file-export', 131, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(57, 'reports.export', 'Export Reports', 'Export report data', 'reports', NULL, 'export', 'fa-download', 132, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(58, 'reports.schedule', 'Schedule Reports', 'Schedule automated reports', 'reports', NULL, 'create', 'fa-clock', 133, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(59, 'settings.view', 'View Settings', 'Access system settings', 'settings', NULL, 'view', 'fa-cog', 150, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(60, 'settings.edit', 'Edit Settings', 'Modify system settings', 'settings', NULL, 'edit', 'fa-cog', 151, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(61, 'users.view', 'View Users', 'View user list', 'users', NULL, 'view', 'fa-users-cog', 160, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(62, 'users.create', 'Create User', 'Add new users', 'users', NULL, 'create', 'fa-user-plus', 161, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(63, 'users.edit', 'Edit User', 'Edit existing users', 'users', NULL, 'edit', 'fa-user-edit', 162, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(64, 'users.delete', 'Delete User', 'Remove users', 'users', NULL, 'delete', 'fa-user-times', 163, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(65, 'permissions.view', 'View Permissions', 'View permission settings', 'permissions', NULL, 'view', 'fa-shield-alt', 170, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(66, 'permissions.manage', 'Manage Permissions', 'Assign and revoke permissions', 'permissions', NULL, 'manage', 'fa-user-lock', 171, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(67, 'permissions.roles', 'Manage Roles', 'Create and edit roles', 'permissions', 'roles', 'manage', 'fa-users-cog', 172, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(68, 'logs.view', 'View Activity Logs', 'Access system activity logs', 'logs', NULL, 'view', 'fa-history', 180, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35'),
(69, 'logs.export', 'Export Logs', 'Export activity logs', 'logs', NULL, 'export', 'fa-download', 181, NULL, 1, 0, '2026-03-16 10:38:35', '2026-03-16 10:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `recent_activities`
--

CREATE TABLE `recent_activities` (
  `activity_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'Staff ID who performed the action',
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Staff name at time of action (denormalized for performance)',
  `user_role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User role at time of action',
  `activity_type` enum('login','logout','failed_login','create','update','delete','view','export','clock_in','clock_out','start_break','end_break','schedule_created','schedule_updated','schedule_cancelled','payroll_generated','payroll_processed','payroll_paid','invoice_generated','invoice_sent','invoice_paid','client_created','client_updated','client_deleted','staff_created','staff_updated','staff_deleted','location_created','location_updated','location_deleted','holiday_created','holiday_updated','holiday_deleted','report_generated','export_completed','import_completed','settings_changed','password_changed','permission_updated') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of activity performed',
  `activity_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Brief title of the activity',
  `activity_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the activity',
  `target_type` enum('staff','client','schedule','payroll','invoice','location','holiday','report','setting','system') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type of target entity',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP address of user',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'Browser/device user agent',
  `device_type` enum('desktop','tablet','mobile','unknown') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_system` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity_date` date GENERATED ALWAYS AS (cast(`created_at` as date)) STORED COMMENT 'Date part for faster queries'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks all user activities across the system for audit and activity feed';

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'Reference to staff/caregiver ID',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Staff email for quick reference',
  `client_id` int NOT NULL COMMENT 'Reference to client ID',
  `schedule_date` date NOT NULL COMMENT 'Date of the scheduled shift',
  `start_time` datetime NOT NULL COMMENT 'Scheduled start time',
  `end_time` datetime NOT NULL COMMENT 'Scheduled end time',
  `clockin_time` datetime DEFAULT NULL COMMENT 'Actual clock in time',
  `clockout_time` datetime DEFAULT NULL COMMENT 'Actual clock out time',
  `shift_type` enum('day','evening','overnight') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'day' COMMENT 'Type of shift',
  `overnight_type` enum('none','rest','awake') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'Overnight shift type if applicable',
  `pay_per_hour` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Hourly pay rate for this shift',
  `status` enum('scheduled','in-progress','completed','cancelled','no-show') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled' COMMENT 'Current status of the shift',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes about the shift',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'User ID who created this schedule',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/Toronto' COMMENT 'Timezone of the clockin/out times',
  `payroll_status` enum('pending','processed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Tracks if schedule has been used in payroll',
  `invoice_status` enum('pending','processed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Tracks if schedule has been used in invoice',
  `payroll_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `payroll_processed_at` datetime DEFAULT NULL,
  `invoice_processed_at` datetime DEFAULT NULL,
  `total_break_hours` int DEFAULT '0' COMMENT 'Total break time in minutes',
  `break_status` enum('none','on_break','returned') COLLATE utf8mb4_unicode_ci DEFAULT 'none' COMMENT 'Current break status',
  `adjusted_clockout_time` datetime DEFAULT NULL COMMENT 'Clock out time adjusted for breaks',
  `net_working_hours` int DEFAULT '0' COMMENT 'Total working minutes minus breaks',
  `holiday_pay` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main table for tracking caregiver shifts and assignments';

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `user_id`, `email`, `client_id`, `schedule_date`, `start_time`, `end_time`, `clockin_time`, `clockout_time`, `shift_type`, `overnight_type`, `pay_per_hour`, `status`, `notes`, `created_at`, `updated_at`, `created_by`, `timezone`, `payroll_status`, `invoice_status`, `payroll_id`, `invoice_id`, `payroll_processed_at`, `invoice_processed_at`, `total_break_hours`, `break_status`, `adjusted_clockout_time`, `net_working_hours`, `holiday_pay`) VALUES
(1, 1, 'john.doe@tamec.com', 1, '2026-03-13', '2026-03-15 06:00:00', '2026-03-15 14:00:00', '2026-03-15 05:55:00', NULL, '', 'none', '45.00', 'in-progress', NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(2, 2, 'sarah.johnson@tamec.com', 1, '2026-03-13', '2026-03-15 06:00:00', '2026-03-15 14:00:00', '2026-03-15 05:50:00', NULL, '', 'none', '35.00', 'in-progress', NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(3, 3, 'michael.chen@tamec.com', 2, '2026-03-13', '2026-03-13 14:00:00', '2026-03-13 22:00:00', '2026-03-13 00:00:00', '2026-03-13 00:00:00', '', 'none', '25.00', 'in-progress', NULL, '2026-03-13 13:38:48', '2026-03-25 23:05:30', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(4, 4, 'maria.garcia@tamec.com', 3, '2026-03-13', '2026-03-13 22:00:00', '2026-03-13 06:00:00', '2026-03-13 21:55:00', '2026-03-13 00:00:00', '', 'none', '55.00', 'in-progress', NULL, '2026-03-13 13:38:48', '2026-03-25 23:05:10', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(5, 1, 'john.doe@tamec.com', 1, '2026-03-26', '2026-03-26 08:00:00', '2026-03-26 08:00:00', NULL, NULL, 'overnight', 'none', '20.00', 'scheduled', NULL, '2026-03-24 16:00:45', '2026-03-24 16:00:45', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(6, 5, 'david.smith@tamec.com', 3, '2026-03-26', '2026-03-26 08:00:00', '2026-03-26 08:00:00', NULL, NULL, 'overnight', 'awake', '20.03', 'scheduled', NULL, '2026-03-24 16:01:59', '2026-03-24 19:26:18', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00'),
(7, 5, 'david.smith@tamec.com', 3, '2026-03-26', '2026-03-26 08:00:00', '2026-03-27 08:00:00', NULL, NULL, 'overnight', 'rest', '20.03', 'scheduled', NULL, '2026-03-24 16:02:33', '2026-03-24 19:26:14', NULL, 'America/Toronto', 'pending', 'pending', NULL, NULL, NULL, NULL, 0, 'none', NULL, 0, '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_audit`
--

CREATE TABLE `schedule_audit` (
  `audit_id` int NOT NULL,
  `schedule_id` int NOT NULL,
  `action` enum('created','updated','cancelled','completed','clocked_in','clocked_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `performed_by` int DEFAULT NULL,
  `performed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_breaks`
--

CREATE TABLE `schedule_breaks` (
  `break_id` int NOT NULL,
  `schedule_id` int NOT NULL,
  `break_start` datetime NOT NULL COMMENT 'When break started',
  `break_end` datetime DEFAULT NULL COMMENT 'When break ended (NULL if still on break)',
  `break_type` enum('lunch','coffee','rest','personal','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lunch',
  `break_duration_hours` int DEFAULT NULL COMMENT 'Calculated duration in minutes',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `staff_id` int NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middlename` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '0' COMMENT 'TRUE = Active, FALSE = Inactive',
  `is_admin` tinyint(1) DEFAULT '0' COMMENT 'TRUE = Admin, FALSE = Regular Staff',
  `role` enum('staff','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Canada',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hire_date` date DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main table for storing caregiver/staff information';

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`staff_id`, `firstname`, `middlename`, `lastname`, `is_active`, `is_admin`, `role`, `address`, `city`, `province`, `postal_code`, `country`, `phone`, `email`, `hire_date`, `password_hash`, `last_login`, `reg_date`, `updated_at`, `created_by`) VALUES
(1, 'John', 'Robert', 'Doe', 1, 1, 'staff', '123 Main Street', 'Toronto', 'Ontario', 'M5V 2T6', 'Canada', '+1-416-555-0123', 'john.doe@tamec.com', '2023-01-15', NULL, NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(2, 'Sarah', 'Elizabeth', 'Johnson', 1, 0, 'admin', '456 Queen Street', 'Toronto', 'Ontario', 'M5V 1A1', 'Canada', '+1-416-555-0456', 'sarah.johnson@tamec.com', '2023-03-20', NULL, NULL, '2026-03-13 13:38:48', '2026-03-16 16:00:05', NULL),
(3, 'Michael', 'James', 'Chen', 1, 0, 'staff', '789 Oak Avenue', 'Mississauga', 'Ontario', 'L5B 3C2', 'Canada', '+1-905-555-0789', 'michael.chen@tamec.com', '2023-06-10', NULL, NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(4, 'Maria', 'Consuelo', 'Garcia', 1, 0, 'staff', '321 Pine Street', 'Toronto', 'Ontario', 'M6H 2N4', 'Canada', '+1-416-555-0321', 'maria.garcia@tamec.com', '2023-09-05', NULL, NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(5, 'David', 'William', 'Smith', 1, 0, 'staff', '654 Maple Drive', 'Brampton', 'Ontario', 'L6Y 4S2', 'Canada', '+1-647-555-0654', 'david.smith@tamec.com', '2023-11-12', NULL, NULL, '2026-03-13 13:38:48', '2026-03-17 14:26:04', NULL),
(7, 'Robert', 'Thomas', 'Brown', 1, 0, 'staff', '147 Birch Lane', 'Vancouver', 'British Columbia', 'V6B 4E3', 'Canada', '+1-604-555-0765', 'robert.brown@tamec.com', '2024-02-14', NULL, NULL, '2026-03-13 13:38:48', '2026-03-13 13:38:48', NULL),
(8, 'Patricia', 'Marie', 'Martinez', 1, 1, 'admin', '258 Spruce Street', 'Calgary', 'Alberta', 'T2P 2M2', 'Canada', '+1-403-555-0432', 'patricia.martinez@tamec.com', '2023-07-22', NULL, NULL, '2026-03-13 13:38:48', '2026-03-16 16:00:11', NULL),
(9, 'Tamec', '', 'Care', 1, 0, 'admin', '654 Maple Drive', 'Brampton', 'Ontario', 'L6Y 4S2', 'Canada', '+1 365 998 6363', 'info@tameccarestaffing.com', NULL, NULL, NULL, '2026-03-17 09:42:13', '2026-03-17 14:42:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_availability`
--

CREATE TABLE `staff_availability` (
  `availability_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `day_of_week` tinyint DEFAULT NULL COMMENT '0=Monday, 6=Sunday',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_certifications`
--

CREATE TABLE `staff_certifications` (
  `certification_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `certification_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issuing_authority` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `document_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_permission_id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `is_allowed` tinyint(1) DEFAULT '1',
  `restrictions` json DEFAULT NULL,
  `granted_by` int DEFAULT NULL,
  `granted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_status` (`is_active`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_mobile` (`mobile`),
  ADD KEY `idx_residential_city` (`residential_city`),
  ADD KEY `idx_billing_city` (`billing_city`),
  ADD KEY `fk_clients_created_by` (`created_by`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`holiday_id`),
  ADD KEY `idx_fixed_date` (`fixed_month`,`fixed_day`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `fk_holidays_created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_period` (`period_start`,`period_end`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `fk_invoices_created_by` (`created_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_staff_id` (`staff_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_postal_code` (`postal_code`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_coordinates` (`latitude`,`longitude`),
  ADD KEY `fk_locations_created_by` (`created_by`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`payroll_id`),
  ADD UNIQUE KEY `payroll_number` (`payroll_number`),
  ADD KEY `idx_period` (`period_start`,`period_end`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_payroll_created_by` (`created_by`);

--
-- Indexes for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_payroll_id` (`payroll_id`),
  ADD KEY `idx_staff_id` (`staff_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_code` (`permission_code`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_permission_code` (`permission_code`);

--
-- Indexes for table `recent_activities`
--
ALTER TABLE `recent_activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_user_name` (`user_name`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activity_date` (`activity_date`),
  ADD KEY `idx_composite` (`activity_date`,`activity_type`,`user_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_schedule_date` (`schedule_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_range` (`schedule_date`,`status`),
  ADD KEY `idx_user_date` (`user_id`,`schedule_date`),
  ADD KEY `idx_client_date` (`client_id`,`schedule_date`),
  ADD KEY `idx_shift_type` (`shift_type`),
  ADD KEY `fk_schedules_created_by` (`created_by`),
  ADD KEY `idx_payroll_status` (`payroll_status`),
  ADD KEY `idx_invoice_status` (`invoice_status`),
  ADD KEY `idx_dual_status` (`payroll_status`,`invoice_status`),
  ADD KEY `fk_schedules_payroll` (`payroll_id`),
  ADD KEY `fk_schedules_invoice` (`invoice_id`),
  ADD KEY `idx_user_id` (`user_id`) USING BTREE,
  ADD KEY `idx_location_id` (`client_id`) USING BTREE;

--
-- Indexes for table `schedule_audit`
--
ALTER TABLE `schedule_audit`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_performed_at` (`performed_at`),
  ADD KEY `fk_audit_performed_by` (`performed_by`);

--
-- Indexes for table `schedule_breaks`
--
ALTER TABLE `schedule_breaks`
  ADD PRIMARY KEY (`break_id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_break_start` (`break_start`),
  ADD KEY `idx_break_end` (`break_end`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_status` (`is_active`),
  ADD KEY `idx_admin` (`is_admin`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_hire_date` (`hire_date`),
  ADD KEY `fk_staff_created_by` (`created_by`);

--
-- Indexes for table `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `idx_staff_schedule` (`staff_id`,`day_of_week`);

--
-- Indexes for table `staff_certifications`
--
ALTER TABLE `staff_certifications`
  ADD PRIMARY KEY (`certification_id`),
  ADD KEY `idx_staff_cert` (`staff_id`),
  ADD KEY `idx_expiry` (`expiry_date`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_permission_id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_permission_id` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `holiday_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `payroll_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_items`
--
ALTER TABLE `payroll_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `recent_activities`
--
ALTER TABLE `recent_activities`
  MODIFY `activity_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `schedule_audit`
--
ALTER TABLE `schedule_audit`
  MODIFY `audit_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_breaks`
--
ALTER TABLE `schedule_breaks`
  MODIFY `break_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `staff_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `staff_availability`
--
ALTER TABLE `staff_availability`
  MODIFY `availability_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_certifications`
--
ALTER TABLE `staff_certifications`
  MODIFY `certification_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `user_permission_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `fk_clients_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `holidays`
--
ALTER TABLE `holidays`
  ADD CONSTRAINT `fk_holidays_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_items_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `fk_locations_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `fk_payroll_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD CONSTRAINT `fk_payroll_items_payroll_id` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`payroll_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payroll_items_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `recent_activities`
--
ALTER TABLE `recent_activities`
  ADD CONSTRAINT `fk_activities_user_id` FOREIGN KEY (`user_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedules_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_schedules_email` FOREIGN KEY (`email`) REFERENCES `staffs` (`email`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedules_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_schedules_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`payroll_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_schedules_user_id` FOREIGN KEY (`user_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_audit`
--
ALTER TABLE `schedule_audit`
  ADD CONSTRAINT `fk_audit_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_schedule_id` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_breaks`
--
ALTER TABLE `schedule_breaks`
  ADD CONSTRAINT `fk_breaks_schedule_id` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `fk_staff_created_by` FOREIGN KEY (`created_by`) REFERENCES `staffs` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD CONSTRAINT `fk_availability_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_certifications`
--
ALTER TABLE `staff_certifications`
  ADD CONSTRAINT `fk_certifications_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
