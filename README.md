Esprit-PIDEV-3A11-2526-AgroFlow

Overview

This project was developed as part of the PIDEV – 3rd Year Engineering Program at Esprit School of Engineering (Academic Year 2025–2026).
It consists of a full-stack web application that enables agricultural cooperatives and agribusinesses to manage farming operations, track field activities, and facilitate collaboration between administrators, agricultural experts, and field employees.

Features

User Module - Sarra Malek 
Two-Factor Authentication (2FA) – Enhanced account security requiring a second verification step during login
Speech-to-Text – Voice input support allowing users to interact with the platform via spoken commands
Text-to-Speech – Audio output functionality that reads content aloud for improved accessibility
Employee Repartition by Terrain – Visual mapping and assignment of employees to specific fields/terrains based on workload and proximity
Role-Based Access Control – Three distinct roles: Super Admin, Agriculture, and Employee 
User Management Dashboard – CRUD operations for users, role assignment, and profile management
Subscription & Offers Management – Farmers can subscribe to one or multiple subscription plans simultaneously, with flexible offer management for different service tiers
Alert System – Automated notifications and alerts for important events, task reminders, subscription renewals, and critical updates

Tech Stack

Frontend

Twig – Symfony's templating engine for dynamic views
Bootstrap – Responsive CSS framework
JavaScript – Client-side interactivity
HTML5 / CSS3 – Standard web technologies
Backend

PHP 8.x – Core programming language
Symfony 6.x – Full-stack web framework (MVC architecture)
Doctrine ORM – Database management and entity mapping
MySQL – Relational database
Composer – PHP dependency manager

Architecture

AgroFlow follows the MVC (Model-View-Controller) architecture provided by Symfony. The application is organized into domain-specific modules with role-based security enforced through Symfony's security component.

Contributors

- Sarra Malek – User Module (2FA, Accessibility, Employee-Terrain Repartition, Subscription & Offers, Alert System)

Academic Context

Developed at Esprit School of Engineering – Tunisia
PIDEV – 3A | 2025–2026

Getting Started

Prerequisites

PHP 8.0 or higher
Composer
MySQL
Symfony CLI (recommended)
Docker (optional) 

Acknowledgment

We would like to express our sincere gratitude to our instructors and supervisors at Esprit School of Engineering for their guidance, support, and valuable feedback throughout the development of this project. We also thank our team members for their collaboration and dedication, as well as the open-source community for providing the tools and frameworks that made this project possible.
