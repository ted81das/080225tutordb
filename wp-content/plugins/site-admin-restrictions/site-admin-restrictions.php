<?php

/*
Plugin Name: Site Admin Restrictions
Plugin URI: https://edgehost.ing
Description: A plugin to create a siteadmin role with restricted capabilities.
Version: 1.33
Author: Edgehost
Author URI: https://edgehost.ing
Text Domain: edgehost_siteadmin
*/

namespace Edgehost_Siteadmin;

function wpturbo_add_capabilities() {
    $role = get_role('siteadmin');

    if (null !== $role) {
        // Add capabilities each time the plugin is activated
        $capabilities = [
            // General capabilities
            'read' => true,
            'edit_posts' => true,
            'edit_pages' => true,
            'delete_posts' => false,
            'delete_pages' => false,
            'edit_others_posts' => true,
            'manage_categories' => true,
            'moderate_comments' => true,
            'edit_files' => true,
            'upload_files' => true,
            'manage_options' => true, // Added capability
            'import' => true, // Added capability
            'export' => true, // Added capability
            'edit_dashboard' => true, // Added capability

            // Post Type Capabilities
            'delete_posts' => true,
            'delete_others_posts' => true,
            'delete_published_posts' => true,
            'delete_private_posts' => true,
            'read_private_posts' => true,
            'edit_posts' => true,
            'edit_others_posts' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'edit_private_posts' => true,

            // Custom Post Types
            'delete_pages' => true,
            'delete_others_pages' => true,
            'delete_published_pages' => true,
            'delete_private_pages' => true,
            'read_private_pages' => true,
            'edit_pages' => true,
            'edit_others_pages' => true,
            'publish_pages' => true,
            'edit_published_pages' => true,
            'edit_private_pages' => true,

            // Tutor LMS Capabilities
            'delete_courses' => true,
            'delete_others_courses' => true,
            'delete_published_courses' => true,
            'delete_private_courses' => true,
            'read_private_courses' => true,
            'edit_courses' => true,
            'edit_others_courses' => true,
            'publish_courses' => true,
            'edit_published_courses' => true,
            'edit_private_courses' => true,

            'delete_lessons' => true,
            'delete_others_lessons' => true,
            'delete_published_lessons' => true,
            'delete_private_lessons' => true,
            'read_private_lessons' => true,
            'edit_lessons' => true,
            'edit_others_lessons' => true,
            'publish_lessons' => true,
            'edit_published_lessons' => true,
            'edit_private_lessons' => true,

            'delete_tutor_quiz' => true,
            'delete_others_tutor_quiz' => true,
            'delete_published_tutor_quiz' => true,
            'delete_private_tutor_quiz' => true,
            'read_private_tutor_quiz' => true,
            'edit_tutor_quiz' => true,
            'edit_others_tutor_quiz' => true,
            'publish_tutor_quiz' => true,
            'edit_published_tutor_quiz' => true,
            'edit_private_tutor_quiz' => true,

            'delete_tutor_assignments' => true,
            'delete_others_tutor_assignments' => true,
            'delete_published_tutor_assignments' => true,
            'delete_private_tutor_assignments' => true,
            'read_private_tutor_assignments' => true,
            'edit_tutor_assignments' => true,
            'edit_others_tutor_assignments' => true,
            'publish_tutor_assignments' => true,
            'edit_published_tutor_assignments' => true,
            'edit_private_tutor_assignments' => true,

            'delete_products' => true,
            'delete_others_products' => true,
            'delete_published_products' => true,
            'delete_private_products' => true,
            'read_private_products' => true,
            'edit_products' => true,
            'edit_others_products' => true,
            'publish_products' => true,
            'edit_published_products' => true,
            'edit_private_products' => true,

            // Additional Tutor Capabilities
            'manage_tutor' => true,
            'manage_tutor_instructor' => true,
            'publish_tutor_courses' => true,
            'publish_tutor_lessons' => true,
            'publish_tutor_questions' => true,
            'publish_tutor_quiz' => true,
            'publish_tutor_quizzes' => true,
            'edit_tutor_course' => true,
            'edit_tutor_courses' => true,
            'edit_tutor_lesson' => true,
            'edit_tutor_lessons' => true,
            'edit_tutor_question' => true,
            'edit_tutor_questions' => true,
            'delete_tutor_course' => true,
            'delete_tutor_courses' => true,
            'delete_tutor_lesson' => true,
            'delete_tutor_lessons' => true,
            'delete_tutor_question' => true,
            'delete_tutor_questions' => true,
            'delete_tutor_quizzes' => true,
            'read_tutor_course' => true,
            'read_tutor_lesson' => true,
            'read_tutor_question' => true,
            'read_tutor_quiz' => true,

            // Manage Bit Integrations
            'bit_integrations_create_integrations' => true,
            'bit_integrations_delete_integrations' => true,
            'bit_integrations_edit_integrations' => true,
            'bit_integrations_manage_integrations' => true,
            'bit_integrations_view_integrations' => true,
        ];

        foreach ($capabilities as $capability => $value) {
            $role->add_cap($capability, $value);
        }

        // Remove specific capabilities
        $capabilities_to_remove = [
            'activate_plugins',
            'install_plugins',
            'edit_plugins',
            'edit_files',
            'edit_themes',
            'upload_plugins', 
            'edit_dashboard', // Added capability to remove
        ];

        foreach ($capabilities_to_remove as $capability) {
            $role->remove_cap($capability);
        }
    }
}

function wpturbo_remove_capabilities() {
    $role = get_role('siteadmin');

    if (null !== $role) {
        // Remove capabilities each time the plugin is deactivated
        $capabilities_to_remove = [
            'activate_plugins',
            'edit_plugins',
            'install_plugins',
            'edit_files',
            'edit_themes',
            'edit_dashboard', // Added capability to remove
            // General capabilities
            'delete_posts',
            'delete_others_posts',
            'delete_published_posts',
            'delete_private_posts',
            'read_private_posts',
            'edit_posts',
            'edit_others_posts',
            'publish_posts',
            'edit_published_posts',
            'edit_private_posts',
            // Custom Post Types
            'delete_pages',
            'delete_others_pages',
            'delete_published_pages',
            'delete_private_pages',
            'read_private_pages',
            'edit_pages',
            'edit_others_pages',
            'publish_pages',
            'edit_published_pages',
            'edit_private_pages',
            // Tutor LMS Capabilities
            'delete_courses',
            'delete_others_courses',
            'delete_published_courses',
            'delete_private_courses',
            'read_private_courses',
            'edit_courses',
            'edit_others_courses',
            'publish_courses',
            'edit_published_courses',
            'edit_private_courses',
            'delete_lessons',
            'delete_others_lessons',
            'delete_published_lessons',
            'delete_private_lessons',
            'read_private_lessons',
            'edit_lessons',
            'edit_others_lessons',
            'publish_lessons',
            'edit_published_lessons',
            'edit_private_lessons',
            'delete_tutor_quiz',
            'delete_others_tutor_quiz',
            'delete_published_tutor_quiz',
            'delete_private_tutor_quiz',
            'read_private_tutor_quiz',
            'edit_tutor_quiz',
            'edit_others_tutor_quiz',
            'publish_tutor_quiz',
            'edit_published_tutor_quiz',
            'edit_private_tutor_quiz',
            'delete_tutor_assignments',
            'delete_others_tutor_assignments',
            'delete_published_tutor_assignments',
            'delete_private_tutor_assignments',
            'read_private_tutor_assignments',
            'edit_tutor_assignments',
            'edit_others_tutor_assignments',
            'publish_tutor_assignments',
            'edit_published_tutor_assignments',
            'edit_private_tutor_assignments',
            'delete_products',
            'delete_others_products',
            'delete_published_products',
            'delete_private_products',
            'read_private_products',
            'edit_products',
            'edit_others_products',
            'publish_products',
            'edit_published_products',
            'edit_private_products',
            // Additional Tutor Capabilities
            'manage_tutor',
            'manage_tutor_instructor',
            'publish_tutor_courses',
            'publish_tutor_lessons',
            'publish_tutor_questions',
            'publish_tutor_quiz',
            'publish_tutor_quizzes',
            'edit_tutor_course',
            'edit_tutor_courses',
            'edit_tutor_lesson',
            'edit_tutor_lessons',
            'edit_tutor_question',
            'edit_tutor_questions',
            'delete_tutor_course',
            'delete_tutor_courses',
            'delete_tutor_lesson',
            'delete_tutor_lessons',
            'delete_tutor_question',
            'delete_tutor_questions',
            'delete_tutor_quizzes',
            'read_tutor_course',
            'read_tutor_lesson',
            'read_tutor_question',
            'read_tutor_quiz',
            // Manage Bit Integrations
            'bit_integrations_create_integrations',
            'bit_integrations_delete_integrations',
            'bit_integrations_edit_integrations',
            'bit_integrations_manage_integrations',
            'bit_integrations_view_integrations',
        ];

        foreach ($capabilities_to_remove as $capability) {
            $role->remove_cap($capability);
        }
    }
}

// Activation hook to add capabilities when the plugin is activated
register_activation_hook(__FILE__, 'Edgehost_Siteadmin\wpturbo_add_capabilities');

// Deactivation hook to remove capabilities when the plugin is deactivated
register_deactivation_hook(__FILE__, 'Edgehost_Siteadmin\wpturbo_remove_capabilities');
