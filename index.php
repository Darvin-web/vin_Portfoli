<?php
session_start();

// Handle success/error messages from submit_message.php
$success = '';
$error = '';

if (isset($_GET['success']) && $_GET['success'] === 'messagesent') {
    $success = "Message sent successfully! I'll get back to you soon.";
} elseif (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'emptyfields':
            $error = "Please fill in all fields.";
            break;
        case 'invalidemail':
            $error = "Please enter a valid email address.";
            break;
        case 'dberror':
            $error = "Failed to send message. Please try again later.";
            break;
        default:
            $error = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Darvin - Full Stack Developer</title>
    <meta name="description" content="Darvin John - Passionate Full Stack Web Developer specializing in modern web technologies">
    <meta name="keywords" content="web developer, php, javascript, react, portfolio">
    <meta property="og:title" content="Darvin - Full Stack Developer">
    <meta property="og:description" content="Passionate Full Stack Web Developer">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Scroll Progress */
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            z-index: 10000;
            width: 0%;
            transition: width 0.1s;
        }

        /* Custom Cursor */
        .cursor {
            width: 20px;
            height: 20px;
            border: 2px solid #667eea;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s, background 0.2s;
            mix-blend-mode: difference;
        }

        .cursor.hover {
            transform: scale(2);
            background: rgba(102, 126, 234, 0.3);
        }

        /* Preloader */
        .preloader {
            position: fixed;
            inset: 0;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            transition: opacity 0.5s, visibility 0.5s;
        }

        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 3px solid transparent;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Navigation */
        nav {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1.2rem 0;
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        nav.scrolled {
            padding: 0.8rem 0;
            background: rgba(10, 10, 10, 0.98);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #a0a0a0;
            font-weight: 500;
            position: relative;
            transition: color 0.3s;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: #ffffff;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .theme-toggle {
            background: none;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            background: #667eea;
            color: white;
        }

        /* Mobile Menu */
        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1001;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background: #fff;
            border-radius: 3px;
            transition: 0.3s;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        .menu-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 998;
        }

        .menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Hero Section - FULL SCREEN */
        .hero {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        #particles-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            min-height: calc(100vh - 160px);
        }

        .hero-text h1 {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1rem;
        }

        .hero-text h1 span {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .typing-text {
            font-size: 2.5rem;
            color: #a0a0a0;
            margin-bottom: 1.5rem;
            height: 3rem;
        }

        .typing-text::after {
            content: '|';
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .hero-text > p {
            color: #a0a0a0;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #ffffff;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        /* Hero Image */
        .hero-image-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-image {
            position: relative;
            width: 400px;
            height: 400px;
        }

        .hero-image::before {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #667eea, #764ba2, #667eea);
            animation: spin 4s linear infinite;
            z-index: -1;
        }

        .hero-image::after {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: conic-gradient(from 360deg, #764ba2, #667eea, #764ba2);
            animation: spin-reverse 3s linear infinite;
            z-index: -1;
            opacity: 0.7;
        }

        @keyframes spin-reverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #0a0a0a;
            position: relative;
            z-index: 1;
        }

        /* Sections - FULL WIDTH */
        section {
            width: 100%;
            padding: 6rem 0;
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 3rem;
            margin-bottom: 6rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 5rem;
            align-items: center;
        }

        .about-image {
            width: 350px;
            height: 450px;
            position: relative;
            margin: 0 auto;
            border-radius: 20px;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .about-image::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background-image: conic-gradient(
                transparent 0deg,
                transparent 90deg,
                #667eea 90deg,
                #764ba2 180deg,
                transparent 180deg,
                transparent 270deg,
                #667eea 270deg,
                #764ba2 360deg
            );
            animation: spin 4s linear infinite;
            z-index: -2;
        }

        .about-image::after {
            content: '';
            position: absolute;
            inset: 5px;
            background: #07182E;
            border-radius: 15px;
            z-index: -1;
        }

        .about-image img {
            position: absolute;
            inset: 5px;
            width: calc(100% - 10px);
            height: calc(100% - 10px);
            object-fit: cover;
            border-radius: 15px;
            z-index: 1;
        }

        .about-text h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .about-text p {
            color: #a0a0a0;
            margin-bottom: 1.5rem;
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .about-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .stat:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .stat h4 {
            font-size: 2.5rem;
            color: #667eea;
            display: inline;
        }

        .stat .plus {
            font-size: 1.5rem;
            color: #764ba2;
        }

        .stat p {
            color: #888;
            margin-top: 0.5rem;
        }

        /* Experience Timeline */
        #experience {
            background: #1a1a1a;
        }

        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, #667eea, #764ba2);
        }

        .timeline-item {
            position: relative;
            padding: 2rem 0;
            width: 50%;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
            padding-right: 3rem;
            text-align: right;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
            padding-left: 3rem;
        }

        .timeline-dot {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #667eea;
            border-radius: 50%;
            top: 2.5rem;
            border: 4px solid #0a0a0a;
        }

        .timeline-item:nth-child(odd) .timeline-dot {
            right: -10px;
        }

        .timeline-item:nth-child(even) .timeline-dot {
            left: -10px;
        }

        .timeline-content {
            background: #0a0a0a;
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .timeline-content .date {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .timeline-content h4 {
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .timeline-content p {
            color: #a0a0a0;
        }

        /* Skills Section */
        #skills {
            background: #0a0a0a;
            position: relative;
        }

        .skills-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .skills-container {
            perspective: 1000px;
            height: 450px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .skills-carousel {
            position: relative;
            width: 300px;
            height: 400px;
            transform-style: preserve-3d;
            transition: transform 0.8s;
        }

        .skill-card {
            position: absolute;
            width: 280px;
            height: 360px;
            left: 10px;
            top: 20px;
            border-radius: 20px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transform-style: preserve-3d;
        }

        .card-rotating-border {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            overflow: hidden;
            z-index: -2;
        }

        .card-rotating-border::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            top: -25%;
            left: -25%;
            background-image: conic-gradient(
                transparent 0deg,
                transparent 90deg,
                #667eea 90deg,
                #764ba2 180deg,
                transparent 180deg,
                transparent 270deg,
                #667eea 270deg,
                #764ba2 360deg
            );
            animation: spin 4s linear infinite;
        }

        .card-rotating-border::after {
            content: '';
            position: absolute;
            inset: 5px;
            background: #07182E;
            border-radius: 15px;
        }

        .card-content {
            position: absolute;
            inset: 5px;
            width: calc(100% - 10px);
            height: calc(100% - 10px);
            background: #07182E;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1;
            padding: 20px;
        }

        .skill-card i {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .skill-card h3 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 10px;
        }

        .skill-card p {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        .skill-card:nth-child(1) { transform: rotateY(0deg) translateZ(350px); }
        .skill-card:nth-child(2) { transform: rotateY(51.4deg) translateZ(350px); }
        .skill-card:nth-child(3) { transform: rotateY(102.8deg) translateZ(350px); }
        .skill-card:nth-child(4) { transform: rotateY(154.2deg) translateZ(350px); }
        .skill-card:nth-child(5) { transform: rotateY(205.6deg) translateZ(350px); }
        .skill-card:nth-child(6) { transform: rotateY(257deg) translateZ(350px); }
        .skill-card:nth-child(7) { transform: rotateY(308.4deg) translateZ(350px); }

        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .nav-dot:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: scale(1.2);
        }

        .nav-dot.active {
            background: #667eea;
            box-shadow: 0 0 20px #667eea;
        }

        .instructions {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Projects Section */
        #projects {
            background: #1a1a1a;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .project-card {
            background: #0a0a0a;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .project-image {
            height: 220px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
        }

        .project-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .project-card:hover .project-overlay {
            opacity: 1;
        }

        .project-overlay a {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            text-decoration: none;
            transition: transform 0.3s;
        }

        .project-overlay a:hover {
            transform: scale(1.1);
        }

        .project-info {
            padding: 1.5rem;
        }

        .project-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }

        .project-info p {
            color: #a0a0a0;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .project-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tag {
            padding: 0.3rem 0.8rem;
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Testimonials */
        #testimonials {
            background: #0a0a0a;
        }

        .testimonials-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .testimonial {
            background: #1a1a1a;
            padding: 3rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            display: none;
        }

        .testimonial.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .testimonial img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 1.5rem;
            border: 3px solid #667eea;
        }

        .testimonial p {
            font-style: italic;
            color: #a0a0a0;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            line-height: 1.6;
        }

        .testimonial h4 {
            color: #667eea;
            margin-bottom: 0.3rem;
        }

        .testimonial small {
            color: #888;
        }

        .testimonial-dots {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: #667eea;
        }

        /* Certificates */
        #certificates {
            background: #1a1a1a;
        }

        .certs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .cert-card {
            background: #0a0a0a;
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s;
        }

        .cert-card:hover {
            border-color: #667eea;
            transform: translateX(10px);
        }

        .cert-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .cert-info h4 {
            margin-bottom: 0.3rem;
            color: #ffffff;
        }

        .cert-info p {
            color: #888;
            font-size: 0.9rem;
        }

        /* Contact Section */
        #contact {
            background: #0a0a0a;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.5rem;
            background: #1a1a1a;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s;
        }

        .contact-item:hover {
            border-color: #667eea;
            transform: translateX(10px);
            background: rgba(102, 126, 234, 0.1);
        }

        .contact-item i {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .contact-item h4 {
            margin-bottom: 0.3rem;
        }

        .contact-item p {
            color: #a0a0a0;
            font-size: 0.9rem;
        }

        .contact-form-wrapper {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.5rem;
            background: #0a0a0a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        /* Footer */
        footer {
            background: #1a1a1a;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 0 1rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-links a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-5px);
        }

        .footer-links {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-links a {
            color: #a0a0a0;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #667eea;
        }

        .copyright {
            color: #888;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
        }

        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 3rem;
            }

            .hero-text > p {
                margin: 0 auto 2rem;
            }

            .hero-image {
                width: 300px;
                height: 300px;
                margin: 0 auto;
            }

            .about-content,
            .contact-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .about-image {
                width: 280px;
                height: 360px;
            }

            .timeline::before {
                left: 20px;
            }

            .timeline-item {
                width: 100%;
                left: 0 !important;
                padding-left: 60px !important;
                padding-right: 0 !important;
                text-align: left !important;
            }

            .timeline-dot {
                left: 10px !important;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 70%;
                height: 100vh;
                background: #0a0a0a;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                gap: 2rem;
                transition: right 0.3s ease;
                z-index: 999;
                padding: 2rem;
            }

            .nav-links.active {
                right: 0;
            }

            .menu-toggle {
                display: flex;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .typing-text {
                font-size: 1.8rem;
                height: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .contact-content {
                grid-template-columns: 1fr;
            }

            .cursor {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-image {
                width: 250px;
                height: 250px;
            }

            .about-image {
                width: 240px;
                height: 320px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Resume Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    backdrop-filter: blur(5px);
}

.modal.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-box {
    background: #1a1a1a;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    width: 100%;
    max-width: 800px;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
}

.modal-head {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.modal-head h3 {
    color: #fff;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-head h3 i {
    color: #667eea;
}

.modal-close {
    background: none;
    border: none;
    color: #888;
    font-size: 1.8rem;
    cursor: pointer;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s;
}

.modal-close:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.modal-body {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.modal-foot {
    padding: 1.5rem 2rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    background: #252525;
}

/* Resume Content Styles */
.resume-content {
    color: #e0e0e0;
}

.resume-header {
    text-align: center;
    padding-bottom: 2rem;
    border-bottom: 2px solid rgba(102, 126, 234, 0.3);
    margin-bottom: 2rem;
}

.resume-header h1 {
    font-size: 2.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}

.resume-title {
    font-size: 1.2rem;
    color: #a0a0a0;
    margin-bottom: 1rem;
}

.resume-contact {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: #888;
}

.resume-contact span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.resume-contact i {
    color: #667eea;
}

.resume-section {
    margin-bottom: 2rem;
}

.resume-section h4 {
    color: #667eea;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.resume-section h4 i {
    width: 20px;
}

.resume-section p {
    color: #a0a0a0;
    line-height: 1.8;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.skill-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.resume-item {
    margin-bottom: 1.5rem;
    padding-left: 1rem;
    border-left: 3px solid #667eea;
}

.resume-item strong {
    color: #fff;
    font-size: 1rem;
    display: block;
    margin-bottom: 0.3rem;
}

.resume-date {
    color: #667eea;
    font-size: 0.85rem;
    display: block;
    margin-bottom: 0.3rem;
}

.tech-tag {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
    padding: 0.2rem 0.6rem;
    border-radius: 15px;
    font-size: 0.75rem;
    margin-right: 0.3rem;
}

.resume-item p {
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.cert-list {
    list-style: none;
}

.cert-list li {
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cert-list li:last-child {
    border-bottom: none;
}

.cert-list span {
    color: #667eea;
    font-size: 0.85rem;
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    .modal, .modal * {
        visibility: visible;
    }
    .modal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto;
        background: white;
        display: block;
        padding: 0;
    }
    .modal-box {
        max-height: none;
        box-shadow: none;
        border: none;
    }
    .modal-head, .modal-foot {
        display: none;
    }
    .modal-body {
        overflow: visible;
        color: #000;
    }
    .resume-header h1 {
        -webkit-text-fill-color: #000;
        color: #000;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .modal {
        padding: 1rem;
    }
    .modal-box {
        max-height: 90vh;
    }
    .modal-body {
        padding: 1.5rem;
    }
    .resume-header h1 {
        font-size: 1.8rem;
    }
    .resume-contact {
        flex-direction: column;
        gap: 0.5rem;
    }
}
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="loader"></div>
    </div>

    <!-- Custom Cursor -->
    <div class="cursor" id="cursor"></div>

    <!-- Scroll Progress -->
    <div class="progress-bar" id="progressBar"></div>

    <!-- Menu Overlay -->
    <div class="menu-overlay" id="overlay" onclick="toggleMenu()"></div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">Darvin.</div>
            <ul class="nav-links" id="navLinks">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#experience">Experience</a></li>
                <li><a href="#skills">Skills</a></li>
                <li><a href="#projects">Projects</a></li>
                <li><a href="#certificates">Certificates</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <canvas id="particles-canvas"></canvas>
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Hi,<br>I'm <span>Darvin</span></h1>
                    <div class="typing-text" id="typingText"></div>
                    <p>Passionate about creating beautiful and functional web experiences. Specialized in modern web technologies and database management.</p>
                    <div class="hero-buttons">
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Hire Me
                        </a>
                        <a href="#" class="btn btn-secondary" onclick="openResumeModal(); return false;">
                            <i class="fas fa-file-alt"></i> View Resume
                        </a>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <div class="hero-image">
                        <img src="mypicture.jpg" alt="Darvin's Photo">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="section-container">
            <h2 class="section-title">About Me</h2>
            <div class="about-content">
                <div class="about-image">
                    <img src="mypicture.jpg" alt="Profile">
                </div>
                <div class="about-text">
                    <h3>Who I Am</h3>
                    <p>I'm <strong>DARVIN JOHN</strong>, a passionate web developer with a love for creating beautiful and functional websites. I specialize in full-stack development and enjoy turning complex problems into simple, elegant solutions.</p>
                    <p>With expertise in modern web technologies including HTML, CSS, JavaScript, PHP, Java, React, and MySQL, I strive to deliver high-quality work that exceeds expectations.</p>
                    <div class="about-stats">
                        <div class="stat">
                            <h4 class="counter" data-target="2">0</h4><span class="plus">+</span>
                            <p>Years College</p>
                        </div>
                        <div class="stat">
                            <h4 class="counter" data-target="3">0</h4><span class="plus">+</span>
                            <p>Projects Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Experience Timeline -->
    <section id="experience">
        <div class="section-container">
            <h2 class="section-title">Experience & Education</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="date">2026 - Present</span>
                        <h4>BS Information Technology</h4>
                        <p>Nueva Ecija University of Science and Technology</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="date">2025</span>
                        <h4>Freelance Web Developer</h4>
                        <p>Self-Employed - Built responsive websites for local businesses</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="date">2023 - 2024</span>
                        <h4>Information and Communication Texhnology</h4>
                        <p>Senior High School - TVL Track (With Honors)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills">
        <div class="skills-wrapper">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-container">
                <div class="skills-carousel" id="skillsCarousel">
                    <div class="skill-card active" data-index="0">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-html5"></i>
                            <h3>HTML5</h3>
                            <p>Semantic markup</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="1">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-css3-alt"></i>
                            <h3>CSS3</h3>
                            <p>Responsive design</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="2">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-js"></i>
                            <h3>JavaScript</h3>
                            <p>Interactive functionality</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="3">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-java"></i>
                            <h3>Java</h3>
                            <p>Object-oriented programming</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="4">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-php"></i>
                            <h3>PHP</h3>
                            <p>Backend development</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="5">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fas fa-database"></i>
                            <h3>MySQL</h3>
                            <p>Database management</p>
                        </div>
                    </div>
                    <div class="skill-card" data-index="6">
                        <div class="card-rotating-border"></div>
                        <div class="card-content">
                            <i class="fab fa-react"></i>
                            <h3>React</h3>
                            <p>Modern frontend</p>
                        </div>
                    </div>ss
                </div>
            </div>
            <div class="carousel-nav" id="carouselNav"></div>
            <p class="instructions">Click any card to rotate • Drag to spin • Auto-rotates when idle</p>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects">
        <div class="section-container">
            <h2 class="section-title">Featured Projects</h2>
            <div class="projects-grid">
                <div class="project-card">
                    <div class="project-image">
                        <i class="fas fa-shopping-cart"></i>
                        <div class="project-overlay">
                            <a href="#" title="View Live"><i class="fas fa-external-link-alt"></i></a>
                            <a href="#" title="View Code"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="project-info">
                        <h3>E-Commerce Platform</h3>
                        <p>Full-stack online shopping platform with payment integration and admin dashboard.</p>
                        <div class="project-tags">
                            <span class="tag">PHP</span>
                            <span class="tag">MySQL</span>
                            <span class="tag">JavaScript</span>
                        </div>
                    </div>
                </div>
                <div class="project-card">
                    <div class="project-image">
                        <i class="fas fa-tasks"></i>
                        <div class="project-overlay">
                            <a href="#" title="View Live"><i class="fas fa-external-link-alt"></i></a>
                            <a href="#" title="View Code"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="project-info">
                        <h3>Task Management App</h3>
                        <p>Collaborative task manager with real-time updates and team features.</p>
                        <div class="project-tags">
                            <span class="tag">React</span>
                            <span class="tag">Node.js</span>
                        </div>
                    </div>
                </div>
                <div class="project-card">
                    <div class="project-image">
                        <i class="fas fa-hospital"></i>
                        <div class="project-overlay">
                            <a href="#" title="View Live"><i class="fas fa-external-link-alt"></i></a>
                            <a href="#" title="View Code"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="project-info">
                        <h3>Hospital Management System</h3>
                        <p>Complete patient records and appointment scheduling system.</p>
                        <div class="project-tags">
                            <span class="tag">Java</span>
                            <span class="tag">MySQL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
<section id="contact">
    <div class="section-container">
        <h2 class="section-title">Get In Touch</h2>
        <div class="contact-content">
            <div class="contact-info">
                <a href="mailto:djohnbanan@email.com" class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p>djohnbanan@email.com</p>
                    </div>
                </a>
                <a href="tel:+639850345730" class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Phone</h4>
                        <p>+63 985 0345 730</p>
                    </div>
                </a>
                <a href="https://maps.google.com/?q=Santa+Barbara+San+Antonio+Nueva+Ecija" target="_blank" class="contact-item">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <h4>Location</h4>
                        <p>San Antonio, Nueva Ecija, Philippines</p>
                    </div>
                </a>
                <a href="https://facebook.com/darvin" target="_blank" class="contact-item">
                    <i class="fab fa-facebook-f" style="background: #1877F2;"></i>
                    <div>
                        <h4>Facebook</h4>
                        <p>Follow my profile</p>
                    </div>
                </a>
            </div>
            <div class="contact-form-wrapper">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form id="contactForm" method="POST" action="submit_message.php">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" name="submit" value="1" class="submit-btn" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="logo">Darvin.</div>
                <div class="social-links">
                    <a href="#" title="GitHub"><i class="fab fa-github"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#projects">Projects</a>
                    <a href="#contact">Contact</a>
                    <a href="#" onclick="openResumeModal(); return false;">View Resume</a>
                </div>
                <div class="copyright">
                    <p>&copy; 2026 Darvin John. All rights reserved.</p>
                    <p style="margin-top: 0.5rem;"><i class="fas fa-code"></i> with <i class="fas fa-heart" style="color: #e74c3c;"></i> in Philippines</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Resume Modal -->
<div id="resumeModal" class="modal" onclick="if(event.target === this) closeResumeModal()">
    <div class="modal-box resume-modal">
        <div class="modal-head">
            <h3><i class="fas fa-file-alt"></i> My Resume</h3>
            <button class="modal-close" onclick="closeResumeModal()">&times;</button>
        </div>
        <div class="modal-body resume-content">
            <!-- Resume Content -->
            <div class="resume-header">
                <h1>DARVIN JOHN</h1>
                <p class="resume-title">Full Stack Web Developer</p>
                <div class="resume-contact">
                    <span><i class="fas fa-envelope"></i> djohnbanan@email.com</span>
                    <span><i class="fas fa-phone"></i> +63 985 0345 730</span>
                    <span><i class="fas fa-location-dot"></i> San Antonio, Nueva Ecija, Philippines</span>
                </div>
            </div>

            <div class="resume-section">
                <h4><i class="fas fa-user"></i> Professional Summary</h4>
                <p>Passionate Full Stack Web Developer with 2+ years of college education in Information Technology and hands-on experience building responsive websites and web applications. Skilled in modern web technologies including PHP, JavaScript, React, Java, Python, and MySQL.</p>
            </div>

            <div class="resume-section">
                <h4><i class="fas fa-code"></i> Technical Skills</h4>
                <div class="skills-list">
                    <span class="skill-badge">HTML5</span>
                    <span class="skill-badge">CSS3</span>
                    <span class="skill-badge">JavaScript</span>
                    <span class="skill-badge">PHP</span>
                    <span class="skill-badge">MySQL</span>
                    <span class="skill-badge">Java</span>
                    <span class="skill-badge">React</span>
                    <span class="skill-badge">Node.js</span>
                    <span class="skill-badge">Python</span>
                </div>
            </div>

            <div class="resume-section">
                <h4><i class="fas fa-graduation-cap"></i> Education</h4>
                <div class="resume-item">
                    <strong>BS Information Technology</strong>
                    <span class="resume-date">2026 - Present</span>
                    <p>Nueva Ecija University of Science and Technology</p>
                </div>
                <div class="resume-item">
                    <strong>Information and communication Technology (TVL Track)</strong>
                    <span class="resume-date">2023 - 2024</span>
                    <p>Senior High School — With Honors</p>
                </div>
            </div>

            <div class="resume-section">
                <h4><i class="fas fa-project-diagram"></i> Projects</h4>
                
                <div class="resume-item">
                    <strong>E-Commerce Platform</strong>
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">MySQL</span>
                    <span class="tech-tag">JavaScript</span>
                    <span class="tech-tag">HTML</span>
                    <span class="tech-tag">CSS</span>
                    <p>Full-stack online shopping platform with payment integration and admin dashboard.</p>
                </div>
                
                <div class="resume-item">
                    <strong>My Portfolio</strong>
                    <span class="tech-tag">HTML</span>
                    <span class="tech-tag">CSS</span>
                    <span class="tech-tag">JavaScript</span>
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">MySQL</span>
                    <p>Full-stack personal portfolio featuring interactive 3D skills carousel, particle effects, contact form with database storage, admin dashboard for message management, and fully responsive dark-themed design.</p>
                </div>
                
                <div class="resume-item">
                    <strong>Brgy. System</strong>
                    <span class="tech-tag">HTML</span>
                    <span class="tech-tag">CSS</span>
                    <span class="tech-tag">JavaScript</span>
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">MySQL</span>
                    <p>Comprehensive barangay information system featuring resident profiling, barangay clearance & certificate requests, blotter records, and official document management with admin dashboard.</p>
                </div>

            <div class="resume-section">
                <h4><i class="fas fa-briefcase"></i> Experience</h4>
                <div class="resume-item">
                    <strong>Freelance Web Developer</strong>
                    <span class="resume-date">2025</span>
                    <p>Self-Employed — Built responsive websites for local businesses.</p>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn btn-secondary" onclick="closeResumeModal()">Close</button>
            <button class="btn btn-primary" onclick="printResume()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

    <script>
        // Preloader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('preloader').classList.add('hidden');
            }, 500);
        });

        // Custom Cursor
        const cursor = document.getElementById('cursor');
        const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;

        if (!isTouchDevice) {
            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX - 10 + 'px';
                cursor.style.top = e.clientY - 10 + 'px';
            });

            document.querySelectorAll('a, button, .project-card, .skill-card, .stat, .cert-card, .contact-item').forEach(el => {
                el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
                el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
            });
        }

        // Scroll Progress & Navbar
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('progressBar').style.width = scrolled + '%';

            const navbar = document.getElementById('navbar');
            if (winScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            const backToTop = document.getElementById('backToTop');
            if (winScroll > 500) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        // Typing Effect
        const roles = ['Web Developer', 'PHP Developer', 'React Developer', 'Full Stack Developer'];
        let roleIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        const typingElement = document.getElementById('typingText');

        function type() {
            const currentRole = roles[roleIndex];
            
            if (isDeleting) {
                typingElement.textContent = currentRole.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typingElement.textContent = currentRole.substring(0, charIndex + 1);
                charIndex++;
            }

            let typeSpeed = isDeleting ? 50 : 100;

            if (!isDeleting && charIndex === currentRole.length) {
                typeSpeed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                roleIndex = (roleIndex + 1) % roles.length;
                typeSpeed = 500;
            }

            setTimeout(type, typeSpeed);
        }

        type();

        // Mobile Menu
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const menuToggle = document.getElementById('menuToggle');
            const overlay = document.getElementById('overlay');
            
            navLinks.classList.toggle('active');
            menuToggle.classList.toggle('active');
            overlay.classList.toggle('active');
            
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        }

        // Close menu on link click
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
                document.getElementById('menuToggle').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Skills Carousel
        const carousel = document.getElementById('skillsCarousel');
        const cards = document.querySelectorAll('.skill-card');
        const navContainer = document.getElementById('carouselNav');
        const cardCount = cards.length;
        const anglePerCard = 360 / cardCount;
        let currentRotation = 0;
        let isDragging = false;
        let startX = 0;
        let currentX = 0;

        // Create dots
        cards.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'nav-dot' + (index === 0 ? ' active' : '');
            dot.addEventListener('click', () => rotateTo(index));
            navContainer.appendChild(dot);
        });

        const dots = document.querySelectorAll('.nav-dot');

        function updateCarousel() {
            carousel.style.transform = `rotateY(${currentRotation}deg)`;
            
            cards.forEach((card, index) => {
                let cardAngle = (index * anglePerCard - currentRotation) % 360;
                if (cardAngle < 0) cardAngle += 360;
                if (cardAngle > 180) cardAngle -= 360;
                
                const isFront = Math.abs(cardAngle) < anglePerCard / 2;
                card.classList.toggle('active', isFront);
                card.style.zIndex = Math.round(100 - Math.abs(cardAngle));
            });

            const activeIndex = Math.round((-currentRotation / anglePerCard) % cardCount);
            const normalizedIndex = ((activeIndex % cardCount) + cardCount) % cardCount;
            
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === normalizedIndex);
            });
        }

        function rotateTo(index) {
            currentRotation = -index * anglePerCard;
            updateCarousel();
        }

        // Click cards
        cards.forEach((card, index) => {
            card.addEventListener('click', (e) => {
                e.stopPropagation();
                let cardAngle = (index * anglePerCard - currentRotation) % 360;
                if (cardAngle < 0) cardAngle += 360;
                
                if (cardAngle < anglePerCard / 2 || cardAngle > 360 - anglePerCard / 2) {
                    currentRotation -= 180;
                } else {
                    rotateTo(index);
                }
                updateCarousel();
            });
        });

        // Drag
        const skillsContainer = document.querySelector('.skills-container');

        skillsContainer.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            skillsContainer.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            currentX = e.clientX;
            const diff = currentX - startX;
            carousel.style.transform = `rotateY(${currentRotation + diff * 0.5}deg)`;
        });

        document.addEventListener('mouseup', (e) => {
            if (!isDragging) return;
            isDragging = false;
            skillsContainer.style.cursor = 'grab';
            const diff = e.clientX - startX;
            currentRotation += diff * 0.5;
            const snapAngle = Math.round(currentRotation / anglePerCard) * anglePerCard;
            currentRotation = snapAngle;
            updateCarousel();
        });

        // Touch
        skillsContainer.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].clientX;
        });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
            const diff = currentX - startX;
            carousel.style.transform = `rotateY(${currentRotation + diff * 0.5}deg)`;
        });

        document.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            const diff = currentX - startX;
            currentRotation += diff * 0.5;
            const snapAngle = Math.round(currentRotation / anglePerCard) * anglePerCard;
            currentRotation = snapAngle;
            updateCarousel();
        });

        // Auto-rotate
        let autoRotateInterval;
        function startAutoRotate() {
            autoRotateInterval = setInterval(() => {
                if (!isDragging) {
                    currentRotation -= 0.2;
                    updateCarousel();
                }
            }, 50);
        }

        function stopAutoRotate() {
            clearInterval(autoRotateInterval);
        }

        let inactivityTimer;
        function resetInactivityTimer() {
            stopAutoRotate();
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(startAutoRotate, 3000);
        }

        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);
        document.addEventListener('touchstart', resetInactivityTimer);

        updateCarousel();
        resetInactivityTimer();

        // Keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                currentRotation += anglePerCard;
                updateCarousel();
                resetInactivityTimer();
            } else if (e.key === 'ArrowRight') {
                currentRotation -= anglePerCard;
                updateCarousel();
                resetInactivityTimer();
            }
        });

        // Testimonials
        let currentTestimonial = 0;
        const testimonials = document.querySelectorAll('.testimonial');
        const testimonialDots = document.querySelectorAll('.testimonial-dots .dot');

        function showTestimonial(index) {
            testimonials.forEach((t, i) => {
                t.classList.remove('active');
                testimonialDots[i].classList.remove('active');
            });
            testimonials[index].classList.add('active');
            testimonialDots[index].classList.add('active');
            currentTestimonial = index;
        }

        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }, 5000);

        // Counter Animation
        const counters = document.querySelectorAll('.counter');
        
        function animateCounters() {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / 50;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(animateCounters, 30);
                } else {
                    counter.innerText = target;
                }
            });
        }

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelector('.about-stats')?.querySelectorAll('.counter').forEach(counter => {
            counterObserver.observe(counter);
        });

        // Particles
        const canvas = document.getElementById('particles-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 1;
                this.speedX = Math.random() * 1 - 0.5;
                this.speedY = Math.random() * 1 - 0.5;
                this.opacity = Math.random() * 0.5 + 0.2;
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x > canvas.width) this.x = 0;
                if (this.x < 0) this.x = canvas.width;
                if (this.y > canvas.height) this.y = 0;
                if (this.y < 0) this.y = canvas.height;
            }

            draw() {
                ctx.fillStyle = `rgba(102, 126, 234, ${this.opacity})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < 50; i++) {
                particles.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });

            particles.forEach((a, index) => {
                particles.slice(index + 1).forEach(b => {
                    const dx = a.x - b.x;
                    const dy = a.y - b.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < 100) {
                        ctx.strokeStyle = `rgba(102, 126, 234, ${0.2 * (1 - distance / 100)})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b.x, b.y);
                        ctx.stroke();
                    }
                });
            });

            requestAnimationFrame(animateParticles);
        }

        resizeCanvas();
        initParticles();
        animateParticles();
        window.addEventListener('resize', resizeCanvas);

        // Back to Top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.getElementById('navLinks').classList.remove('active');
                document.getElementById('menuToggle').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Form submission feedback
        document.getElementById('contactForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        });

        // Resume Modal Functions
function openResumeModal() {
    document.getElementById('resumeModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeResumeModal() {
    document.getElementById('resumeModal').classList.remove('active');
    document.body.style.overflow = '';
}

function printResume() {
    window.print();
}

// Close on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeResumeModal();
    }
});
    </script>
</body>
</html>