# Online Tech Magazine

**Author:** Tint Sabei Soe Win  
**Group:** 232

## Project Description
The **Online Tech Magazine** is a dynamic web application designed to provide readers with the latest articles, news, and reviews in the field of technology, software, and innovation. The main goal of the project is to create a functional and interactive platform where users can easily access, publish, and manage digital content.

The system is developed using **PHP** and **MySQL**, supporting full CRUD operations (Create, Read, Update, Delete) to manage data efficiently. These operations are implemented through a reusable interface to avoid repetitive code and ensure maintainable functionality.

## Purpose
This website serves as a hub for technology enthusiasts, offering news, reviews, and informative articles. It allows both readers and content creators to interact and engage with the platform seamlessly.

## Target Users
- **Visitors:** Can browse articles, read comments, and send messages via the contact form.  
- **Registered Authors:** Can create, edit, and delete their own articles, post comments, and access basic analytics.  
- **Administrators:** Have full control over all articles, users, comments, and reports, including site analytics and external content management.

## Main Features
- Browse articles by category  
- Create, edit, delete articles (for authors and admins)  
- Post comments on articles  
- Contact form to send messages to administrators  
- Analytics and reporting of visits, popular articles, and user activity  
- Integration of external content from technology news sources

## Architecture
The Online Tech Magazine is structured with the following components:

### Roles / Actors
- **Visitor:** Unregistered user, can browse content and send messages.  
- **Author:** Registered user, can manage their own articles and post comments.  
- **Administrator:** Full control over articles, users, comments, analytics, and external content.

### Main Entities (Database Tables)
- **USER:** Stores information about users, including role and registration date.  
- **ARTICLE:** Contains articles with title, content, image, category, and author.  
- **CATEGORY:** Defines categories for articles (e.g., AI, Gadgets).  
- **COMMENT:** Stores comments posted by users on articles.  
- **VISIT:** Records user visits for analytics.  
- **MESSAGE:** Stores messages sent via the contact form.

### Relationships
- **USER → ARTICLE:** One user can write multiple articles (1:N)  
- **CATEGORY → ARTICLE:** One category can contain many articles (1:N)  
- **ARTICLE → COMMENT:** One article can have multiple comments (1:N)  
- **USER → COMMENT:** One user can post multiple comments (1:N)  
- **USER → VISIT:** One user can have multiple visits (0:N, guest visits possible)  
- **USER → MESSAGE:** One user can send multiple messages (0:N)

### Main Components
- **Frontend Pages:** Homepage, article view, categories, user profile, admin dashboard  
- **Backend Scripts:** PHP scripts handling CRUD operations, login, sessions, analytics  
- **Database:** MariaDB storing all entities and relationships

## UML Diagrams
The following diagrams illustrate the system architecture and main flows:

1. Class diagram (database / domain entities)  
2. Use case diagram (actors and main use cases)  
3. Sequence diagram — Login flow  
4. Sequence diagram — Publish Article (Author creates article)  
5. Sequence diagram — Post Comment  

*(UML diagrams can be added as images in the repo and linked here.)*

## Functionalities Overview
- **User Registration & Authentication:** Secure login/logout and session management  
- **Role-Based Access Control:** Different permissions for visitors, authors, and administrators  
- **Article Management:** Full CRUD for authors and admins  
- **Category Management:** Admins can manage categories  
- **Commenting System:** Users can comment, admins moderate  
- **Analytics & Reports:** Track visits, article popularity, user activity  
- **Contact Form:** Allows sending messages to administrators  
- **External Content Integration:** Import and display content from external tech sources  
- **Session Termination:** Automatic logout for security

---


