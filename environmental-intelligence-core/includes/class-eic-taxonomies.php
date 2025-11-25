<?php
/**
 * Custom Taxonomies Registration
 *
 * Registers custom taxonomies for the Environmental Intelligence Core.
 * Provides classification and filtering capabilities for environmental data.
 *
 * @package EnvironmentalIntelligenceCore
 * @subpackage Taxonomies
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EIC_Taxonomies Class
 */
class EIC_Taxonomies {

    /**
     * Initialize taxonomies
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'insert_default_terms' ), 10 );
    }

    /**
     * Register core taxonomies
     */
    public static function register_taxonomies() {
        
        if ( ! is_blog_installed() || taxonomy_exists( 'contaminant' ) ) {
            return;
        }

        // Register Contaminant Taxonomy
        register_taxonomy(
            'contaminant',
            array( 'superfund_site' ),
            apply_filters(
                'eic_register_taxonomy_contaminant',
                array(
                    'labels' => array(
                        'name' => __( 'Contaminants', 'env-intel-core' ),
                        'singular_name' => __( 'Contaminant', 'env-intel-core' ),
                        'menu_name' => _x( 'Contaminants', 'Admin menu name', 'env-intel-core' ),
                        'search_items' => __( 'Search Contaminants', 'env-intel-core' ),
                        'all_items' => __( 'All Contaminants', 'env-intel-core' ),
                        'parent_item' => __( 'Parent Contaminant', 'env-intel-core' ),
                        'parent_item_colon' => __( 'Parent Contaminant:', 'env-intel-core' ),
                        'edit_item' => __( 'Edit Contaminant', 'env-intel-core' ),
                        'update_item' => __( 'Update Contaminant', 'env-intel-core' ),
                        'add_new_item' => __( 'Add New Contaminant', 'env-intel-core' ),
                        'new_item_name' => __( 'New Contaminant Name', 'env-intel-core' ),
                    ),
                    'description' => __( 'Environmental contaminants found at sites', 'env-intel-core' ),
                    'public' => true,
                    'hierarchical' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'show_in_nav_menus' => true,
                    'show_in_rest' => true,
                    'show_tagcloud' => true,
                    'show_in_quick_edit' => true,
                    'show_admin_column' => true,
                    'rewrite' => array( 'slug' => 'contaminant', 'with_front' => false ),
                )
            )
        );

        // Register Environmental Justice Zone Taxonomy
        register_taxonomy(
            'environmental_justice_zone',
            array( 'superfund_site' ),
            apply_filters(
                'eic_register_taxonomy_ej_zone',
                array(
                    'labels' => array(
                        'name' => __( 'Environmental Justice Zones', 'env-intel-core' ),
                        'singular_name' => __( 'Environmental Justice Zone', 'env-intel-core' ),
                        'menu_name' => _x( 'EJ Zones', 'Admin menu name', 'env-intel-core' ),
                        'search_items' => __( 'Search EJ Zones', 'env-intel-core' ),
                        'all_items' => __( 'All EJ Zones', 'env-intel-core' ),
                        'edit_item' => __( 'Edit EJ Zone', 'env-intel-core' ),
                        'update_item' => __( 'Update EJ Zone', 'env-intel-core' ),
                        'add_new_item' => __( 'Add New EJ Zone', 'env-intel-core' ),
                        'new_item_name' => __( 'New EJ Zone Name', 'env-intel-core' ),
                    ),
                    'description' => __( 'Environmental justice zones for vulnerable communities', 'env-intel-core' ),
                    'public' => true,
                    'hierarchical' => false,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'show_in_nav_menus' => true,
                    'show_in_rest' => true,
                    'show_tagcloud' => false,
                    'show_in_quick_edit' => true,
                    'show_admin_column' => true,
                    'rewrite' => array( 'slug' => 'ej-zone', 'with_front' => false ),
                )
            )
        );
    }

    /**
     * Insert default taxonomy terms
     */
    public static function insert_default_terms() {
        
        // Only insert once
        if ( get_option( 'eic_default_terms_inserted' ) ) {
            return;
        }

        // Default Contaminants
        $contaminants = array(
            'Heavy Metals' => array(
                'Lead',
                'Arsenic',
                'Mercury',
                'Cadmium',
                'Chromium',
            ),
            'Volatile Organic Compounds (VOCs)' => array(
                'Trichloroethylene (TCE)',
                'Perchloroethylene (PCE)',
                'Benzene',
                'Toluene',
                'Vinyl Chloride',
            ),
            'Petroleum Products' => array(
                'Gasoline',
                'Diesel',
                'Oil',
                'BTEX',
            ),
            'Pesticides & Herbicides' => array(
                'DDT',
                'Chlordane',
                'Dioxins',
                'PCBs',
            ),
            'Other' => array(
                'Asbestos',
                'Radioactive Materials',
                'PFAS',
            ),
        );

        foreach ( $contaminants as $parent => $children ) {
            // Insert parent term
            $parent_term = wp_insert_term( $parent, 'contaminant' );
            
            if ( ! is_wp_error( $parent_term ) ) {
                $parent_id = $parent_term['term_id'];
                
                // Insert child terms
                foreach ( $children as $child ) {
                    wp_insert_term(
                        $child,
                        'contaminant',
                        array( 'parent' => $parent_id )
                    );
                }
            }
        }

        // Default Environmental Justice Zones
        $ej_zones = array(
            'CalEnviroScreen High Priority',
            'Disadvantaged Community',
            'Low-Income Community',
            'Tribal Land',
            'Historically Redlined Area',
        );

        foreach ( $ej_zones as $zone ) {
            wp_insert_term( $zone, 'environmental_justice_zone' );
        }

        // Mark as inserted
        update_option( 'eic_default_terms_inserted', true );
    }
}
