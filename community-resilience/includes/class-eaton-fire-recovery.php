<?php
/**
 * Eaton Fire Recovery Resource Platform
 * 
 * Comprehensive resource directory and tracking system for Eaton Fire
 * recovery efforts in Altadena and surrounding communities.
 *
 * Based on "Altadena's Unmet Needs" - A Collab and Care Report 2025
 *
 * @package ThrivingRoots
 * @subpackage CommunityResilience
 */

if (!defined('ABSPATH')) {
    exit;
}

class Eaton_Fire_Recovery {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_custom_post_types'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_shortcode('eaton_fire_resources', array($this, 'resources_directory_shortcode'));
        add_shortcode('altadena_unmet_needs', array($this, 'unmet_needs_shortcode'));
        add_shortcode('fire_recovery_tracker', array($this, 'recovery_tracker_shortcode'));
        add_shortcode('environmental_safety_post_fire', array($this, 'environmental_safety_shortcode'));
    }
    
    /**
     * Register custom post types
     */
    public function register_custom_post_types() {
        // Fire Recovery Resource CPT
        register_post_type('fire_recovery_resource', array(
            'labels' => array(
                'name' => __('Fire Recovery Resources', 'thriving-roots'),
                'singular_name' => __('Fire Recovery Resource', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'menu_icon' => 'dashicons-sos',
            'taxonomies' => array('need_category', 'resource_status'),
        ));
        
        // Need Category Taxonomy
        register_taxonomy('need_category', 'fire_recovery_resource', array(
            'labels' => array(
                'name' => __('Need Categories', 'thriving-roots'),
                'singular_name' => __('Need Category', 'thriving-roots'),
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'public' => true,
        ));
        
        // Resource Status Taxonomy
        register_taxonomy('resource_status', 'fire_recovery_resource', array(
            'labels' => array(
                'name' => __('Resource Status', 'thriving-roots'),
                'singular_name' => __('Status', 'thriving-roots'),
            ),
            'hierarchical' => false,
            'show_in_rest' => true,
            'public' => true,
        ));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('thriving-roots/v1', '/fire-recovery/resources', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_fire_recovery_resources'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('thriving-roots/v1', '/fire-recovery/needs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_unmet_needs'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Get unmet needs data
     */
    public function get_unmet_needs($request) {
        $category = $request->get_param('category');
        
        // Altadena's Unmet Needs - 54 needs across 8 categories
        $needs = array(
            'care_management' => array(
                'title' => 'Care Management',
                'needs' => array(
                    array(
                        'id' => 1,
                        'title' => 'In-person support for impacted elders',
                        'current_efforts' => array('211 LA', 'Hope Crisis', 'Hope Now CRC Senior Program', 'Operation Hope'),
                        'gaps' => array(
                            'Organized list of homebound elders',
                            'Help with moving furniture and cleaning',
                            'Outreach for non-English speakers',
                            'Expanded mental health services',
                        ),
                        'priority' => 'high',
                    ),
                    array(
                        'id' => 2,
                        'title' => 'In-person support for displaced families outside the area',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Mobile support teams',
                            'Virtual case management',
                            'Transportation assistance',
                        ),
                        'priority' => 'high',
                    ),
                    array(
                        'id' => 3,
                        'title' => 'Reach under-networked families',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Door-to-door outreach',
                            'Multi-language support',
                            'Trusted community connectors',
                        ),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 4,
                        'title' => 'Consistent long-term case management',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Dedicated case managers',
                            'Follow-up system',
                            'Coordination across agencies',
                        ),
                        'priority' => 'high',
                    ),
                    array(
                        'id' => 5,
                        'title' => 'Real-time, vetted resource information',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Centralized information hub',
                            'Regular updates',
                            'Verification system',
                        ),
                        'priority' => 'high',
                    ),
                ),
            ),
            'essential_needs' => array(
                'title' => 'Essential Needs',
                'needs' => array(
                    array(
                        'id' => 6,
                        'title' => 'Shared calendar for essential needs distributions',
                        'current_efforts' => array(),
                        'gaps' => array('Centralized calendar', 'Category-based tracking'),
                        'priority' => 'medium',
                    ),
                    array(
                        'id' => 7,
                        'title' => 'No more standing in lines for essential needs',
                        'current_efforts' => array(),
                        'gaps' => array('Delivery systems', 'Pre-registration', 'Mobile distribution'),
                        'priority' => 'high',
                    ),
                    array(
                        'id' => 8,
                        'title' => 'Mail forwarding confirmed for all displaced families',
                        'current_efforts' => array(),
                        'gaps' => array('USPS coordination', 'Verification system'),
                        'priority' => 'medium',
                    ),
                ),
            ),
            'rehousing' => array(
                'title' => 'Rehousing & Financial Support',
                'needs' => array(
                    array(
                        'id' => 9,
                        'title' => 'Complete count of families without stable rehousing',
                        'current_efforts' => array(),
                        'gaps' => array('Comprehensive survey', 'Data coordination with CALOES/FEMA'),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 10,
                        'title' => 'Black, Brown, and most vulnerable prioritized for rehousing',
                        'current_efforts' => array(),
                        'gaps' => array('Equity-based allocation', 'Priority system'),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 20,
                        'title' => 'Full access to insurance payouts',
                        'current_efforts' => array(),
                        'gaps' => array('Insurance advocacy', 'Legal support'),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 22,
                        'title' => 'Long-term direct cash assistance',
                        'current_efforts' => array(),
                        'gaps' => array('Sustained funding', 'Easy access process'),
                        'priority' => 'high',
                    ),
                ),
            ),
            'land_rebuilding' => array(
                'title' => 'Land & Rebuilding',
                'needs' => array(
                    array(
                        'id' => 31,
                        'title' => 'Rebuild cost gaps known and covered',
                        'current_efforts' => array(),
                        'gaps' => array('Cost assessment', 'Gap funding'),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 32,
                        'title' => 'Community control over land and development',
                        'current_efforts' => array('Altadena Earthseed Community Land Trust'),
                        'gaps' => array('Community land trust expansion', 'Anti-displacement policies'),
                        'priority' => 'critical',
                    ),
                    array(
                        'id' => 33,
                        'title' => 'Permits issued for all families',
                        'current_efforts' => array(),
                        'gaps' => array('Expedited permitting', 'Technical assistance'),
                        'priority' => 'high',
                    ),
                ),
            ),
            'community' => array(
                'title' => 'Community Support',
                'needs' => array(
                    array(
                        'id' => 38,
                        'title' => 'Families know Altadena\'s social and political history',
                        'current_efforts' => array(),
                        'gaps' => array('Educational programs', 'Historical preservation'),
                        'priority' => 'medium',
                    ),
                    array(
                        'id' => 41,
                        'title' => 'Trust in fire relief leaders',
                        'current_efforts' => array(),
                        'gaps' => array('Transparent leadership', 'Community accountability'),
                        'priority' => 'high',
                    ),
                ),
            ),
            'environmental' => array(
                'title' => 'Environmental Safety',
                'needs' => array(
                    array(
                        'id' => 49,
                        'title' => 'Safe return and environmental restoration',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Air quality monitoring',
                            'Soil testing',
                            'Water quality testing',
                            'Ash and debris removal',
                            'Health screenings',
                        ),
                        'priority' => 'critical',
                    ),
                ),
            ),
            'worker_protection' => array(
                'title' => 'Protection for Workers',
                'needs' => array(
                    array(
                        'id' => 25,
                        'title' => 'Health and safety for recovery workers',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'PPE provision',
                            'Safety training',
                            'Health monitoring',
                        ),
                        'priority' => 'high',
                    ),
                ),
            ),
            'fire_systems' => array(
                'title' => 'Fire Systems',
                'needs' => array(
                    array(
                        'id' => 50,
                        'title' => 'Fire response accountability and future prevention',
                        'current_efforts' => array(),
                        'gaps' => array(
                            'Investigation of failures',
                            'System improvements',
                            'Community preparedness',
                        ),
                        'priority' => 'high',
                    ),
                ),
            ),
        );
        
        if ($category && isset($needs[$category])) {
            return rest_ensure_response($needs[$category]);
        }
        
        return rest_ensure_response($needs);
    }
    
    /**
     * Shortcode: Eaton Fire Resources Directory
     * 
     * Usage: [eaton_fire_resources]
     */
    public function resources_directory_shortcode($atts) {
        ob_start();
        ?>
        <div class="eaton-fire-resources">
            <h2>🔥 Eaton Fire Recovery Resources</h2>
            <p class="intro">Comprehensive resource directory for families impacted by the Eaton Fire in Altadena and surrounding communities.</p>
            
            <div class="emergency-contacts">
                <h3>🚨 Emergency Contacts</h3>
                <div class="contact-grid">
                    <div class="contact-card">
                        <h4>211 LA</h4>
                        <p>24/7 information and referral services</p>
                        <p><strong>Phone:</strong> <a href="tel:211">211</a></p>
                        <p><strong>Website:</strong> <a href="https://211la.org" target="_blank">211la.org</a></p>
                    </div>
                    <div class="contact-card">
                        <h4>Altadena Rising</h4>
                        <p>Community organizing and resource coordination</p>
                        <p><strong>Email:</strong> altadenarisingnow@gmail.com</p>
                    </div>
                    <div class="contact-card">
                        <h4>FEMA Disaster Assistance</h4>
                        <p>Federal disaster relief</p>
                        <p><strong>Phone:</strong> <a href="tel:1-800-621-3362">1-800-621-3362</a></p>
                        <p><strong>Website:</strong> <a href="https://www.fema.gov" target="_blank">fema.gov</a></p>
                    </div>
                    <div class="contact-card">
                        <h4>LA County Fire Department</h4>
                        <p>Fire safety and prevention</p>
                        <p><strong>Phone:</strong> <a href="tel:323-881-2411">323-881-2411</a></p>
                    </div>
                </div>
            </div>
            
            <div class="resource-categories">
                <h3>📋 Resources by Category</h3>
                
                <div class="category-accordion">
                    <div class="category-section">
                        <h4 class="category-header">🏠 Housing & Shelter</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>Temporary Housing:</strong> FEMA, Red Cross, local hotels</li>
                                <li><strong>Rental Assistance:</strong> LA County Department of Consumer and Business Affairs</li>
                                <li><strong>RV/ADU Placement:</strong> Contact local organizations</li>
                                <li><strong>Senior Housing:</strong> Loma Alta Senior Center, Pasadena Senior Center</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="category-section">
                        <h4 class="category-header">💰 Financial Assistance</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>FEMA Individual Assistance:</strong> Apply at DisasterAssistance.gov</li>
                                <li><strong>SBA Disaster Loans:</strong> Low-interest loans for homeowners and businesses</li>
                                <li><strong>Insurance Claims:</strong> Department of Insurance consumer hotline: 1-800-927-4357</li>
                                <li><strong>Direct Cash Assistance:</strong> Contact local nonprofits</li>
                                <li><strong>Financial Counseling:</strong> Operation Hope</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="category-section">
                        <h4 class="category-header">🏗️ Rebuilding Support</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>Permits:</strong> LA County Department of Public Works</li>
                                <li><strong>Debris Removal:</strong> County-coordinated Right of Entry program</li>
                                <li><strong>Rebuild Cost Estimates:</strong> Local contractors and assessors</li>
                                <li><strong>Community Land Trust:</strong> Altadena Earthseed Community Land Trust</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="category-section">
                        <h4 class="category-header">🧠 Mental Health & Wellness</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>Crisis Counseling:</strong> LA County Department of Mental Health: 1-800-854-7771</li>
                                <li><strong>Grief Support Groups:</strong> Local churches and community centers</li>
                                <li><strong>Youth Services:</strong> Xtreme Athletics, Scouts Troop 40</li>
                                <li><strong>Elder Support:</strong> Hope Now CRC Senior Program</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="category-section">
                        <h4 class="category-header">🌱 Environmental Safety</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>Air Quality Monitoring:</strong> South Coast AQMD</li>
                                <li><strong>Water Testing:</strong> LA County Public Health</li>
                                <li><strong>Soil Testing:</strong> Environmental testing labs</li>
                                <li><strong>Ash Cleanup:</strong> County debris removal program</li>
                                <li><strong>Health Screenings:</strong> Local clinics and health centers</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="category-section">
                        <h4 class="category-header">👨‍👩‍👧‍👦 Family Services</h4>
                        <div class="category-content">
                            <ul>
                                <li><strong>School Enrollment:</strong> Pasadena Unified School District</li>
                                <li><strong>Childcare:</strong> Local childcare providers and subsidies</li>
                                <li><strong>Food Assistance:</strong> Food banks, CalFresh, WIC</li>
                                <li><strong>Legal Aid:</strong> Public Counsel, Legal Aid Foundation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="collaborating-orgs">
                <h3>🤝 Collaborating Organizations</h3>
                <p>42+ local organizations working together to support Altadena's recovery:</p>
                <div class="org-tags">
                    <span class="org-tag">Altadena Rising</span>
                    <span class="org-tag">Altadena CoLab</span>
                    <span class="org-tag">Altadena NAACP</span>
                    <span class="org-tag">Hope Now CRC</span>
                    <span class="org-tag">CHIRLA</span>
                    <span class="org-tag">Community Clergy Coalition</span>
                    <span class="org-tag">Altadena Earthseed CLT</span>
                    <span class="org-tag">Operation Hope</span>
                    <span class="org-tag">YWCA San Gabriel Valley</span>
                    <span class="org-tag">+ 33 more organizations</span>
                </div>
            </div>
            
            <div class="get-help-cta">
                <h3>Need Help?</h3>
                <p>If you or someone you know needs assistance, please reach out:</p>
                <a href="mailto:altadenarisingnow@gmail.com" class="cta-button">Contact Altadena Rising</a>
                <a href="tel:211" class="cta-button">Call 211</a>
            </div>
        </div>
        
        <style>
            .eaton-fire-resources {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .eaton-fire-resources h2 {
                color: #d32f2f;
                text-align: center;
                margin-bottom: 10px;
            }
            .intro {
                text-align: center;
                font-size: 1.1em;
                margin-bottom: 30px;
                color: #666;
            }
            .emergency-contacts {
                background: #fff3e0;
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
                border-left: 5px solid #ff6f00;
            }
            .contact-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .contact-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .contact-card h4 {
                color: #d32f2f;
                margin-top: 0;
            }
            .contact-card p {
                margin: 8px 0;
                font-size: 0.95em;
            }
            .contact-card a {
                color: #1976d2;
                text-decoration: none;
            }
            .resource-categories {
                margin: 30px 0;
            }
            .category-section {
                background: #fff;
                margin-bottom: 15px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .category-header {
                background: #2c5f2d;
                color: #fff;
                padding: 15px 20px;
                margin: 0;
                cursor: pointer;
            }
            .category-header:hover {
                background: #1e4620;
            }
            .category-content {
                padding: 20px;
            }
            .category-content ul {
                padding-left: 20px;
            }
            .category-content li {
                margin: 10px 0;
                line-height: 1.6;
            }
            .collaborating-orgs {
                background: #e8f5e9;
                padding: 30px;
                border-radius: 10px;
                margin: 30px 0;
            }
            .org-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 15px;
            }
            .org-tag {
                background: #fff;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 0.9em;
                border: 1px solid #2c5f2d;
            }
            .get-help-cta {
                text-align: center;
                background: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .cta-button {
                display: inline-block;
                background: #d32f2f;
                color: #fff;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 5px;
                margin: 10px;
                font-weight: bold;
            }
            .cta-button:hover {
                background: #b71c1c;
            }
        </style>
        
        <script>
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function() {
                const content = this.nextElementSibling;
                content.style.display = content.style.display === 'none' ? 'block' : 'none';
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Altadena Unmet Needs
     * 
     * Usage: [altadena_unmet_needs]
     */
    public function unmet_needs_shortcode($atts) {
        $needs = $this->get_unmet_needs(new WP_REST_Request())->data;
        
        ob_start();
        ?>
        <div class="altadena-unmet-needs">
            <h2>Altadena's Unmet Needs</h2>
            <p class="subtitle">A Collab and Care Report - 2025</p>
            <p class="intro">This report surfaces 54 needs across 8 categories. It is a call to action for nonprofits, funded organizations, decisionmakers, and elected leaders to get actively engaged.</p>
            
            <div class="needs-summary">
                <div class="summary-card">
                    <h3>54</h3>
                    <p>Total Needs</p>
                </div>
                <div class="summary-card">
                    <h3>8</h3>
                    <p>Categories</p>
                </div>
                <div class="summary-card">
                    <h3>42+</h3>
                    <p>Organizations</p>
                </div>
            </div>
            
            <div class="needs-by-category">
                <?php foreach ($needs as $category_key => $category): ?>
                <div class="need-category">
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    
                    <?php foreach ($category['needs'] as $need): ?>
                    <div class="need-item priority-<?php echo esc_attr($need['priority']); ?>">
                        <div class="need-header">
                            <h4><?php echo esc_html($need['title']); ?></h4>
                            <span class="priority-badge"><?php echo esc_html(ucfirst($need['priority'])); ?> Priority</span>
                        </div>
                        
                        <?php if (!empty($need['current_efforts'])): ?>
                        <div class="current-efforts">
                            <strong>Current Efforts:</strong>
                            <ul>
                                <?php foreach ($need['current_efforts'] as $effort): ?>
                                <li><?php echo esc_html($effort); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($need['gaps'])): ?>
                        <div class="gaps">
                            <strong>Gaps to Fill:</strong>
                            <ul>
                                <?php foreach ($need['gaps'] as $gap): ?>
                                <li><?php echo esc_html($gap); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <button class="commit-btn" data-need-id="<?php echo esc_attr($need['id']); ?>">I Can Help With This</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="take-action">
                <h3>How to Take Action</h3>
                <ol>
                    <li><strong>Find Your Focus:</strong> Identify the unmet need(s) you'll work to meet</li>
                    <li><strong>Connect & Collaborate:</strong> Email altadenarisingnow@gmail.com to link with others</li>
                    <li><strong>Act on the Gaps:</strong> See where you or your team can help</li>
                    <li><strong>Share For Success:</strong> Get this report in front of funders and leaders</li>
                </ol>
            </div>
        </div>
        
        <style>
            .altadena-unmet-needs {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .altadena-unmet-needs h2 {
                text-align: center;
                color: #2c5f2d;
            }
            .subtitle {
                text-align: center;
                font-style: italic;
                color: #666;
            }
            .intro {
                text-align: center;
                max-width: 800px;
                margin: 20px auto;
                font-size: 1.1em;
            }
            .needs-summary {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .summary-card {
                background: #2c5f2d;
                color: #fff;
                padding: 30px;
                border-radius: 10px;
                text-align: center;
            }
            .summary-card h3 {
                font-size: 3em;
                margin: 0;
            }
            .summary-card p {
                margin: 10px 0 0 0;
                font-size: 1.1em;
            }
            .need-category {
                margin: 40px 0;
            }
            .need-category > h3 {
                background: #2c5f2d;
                color: #fff;
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .need-item {
                background: #fff;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 8px;
                border-left: 5px solid #2c5f2d;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .need-item.priority-critical {
                border-left-color: #d32f2f;
            }
            .need-item.priority-high {
                border-left-color: #ff6f00;
            }
            .need-item.priority-medium {
                border-left-color: #fbc02d;
            }
            .need-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            .need-header h4 {
                margin: 0;
                color: #2c5f2d;
            }
            .priority-badge {
                padding: 5px 15px;
                border-radius: 20px;
                font-size: 0.85em;
                font-weight: bold;
            }
            .priority-critical .priority-badge {
                background: #ffebee;
                color: #d32f2f;
            }
            .priority-high .priority-badge {
                background: #fff3e0;
                color: #ff6f00;
            }
            .priority-medium .priority-badge {
                background: #fffde7;
                color: #f57f17;
            }
            .current-efforts, .gaps {
                margin: 15px 0;
            }
            .current-efforts ul, .gaps ul {
                padding-left: 20px;
                margin: 10px 0;
            }
            .current-efforts li, .gaps li {
                margin: 5px 0;
            }
            .commit-btn {
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-top: 10px;
            }
            .commit-btn:hover {
                background: #1e4620;
            }
            .take-action {
                background: #e8f5e9;
                padding: 30px;
                border-radius: 10px;
                margin-top: 40px;
            }
            .take-action h3 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .take-action ol {
                padding-left: 20px;
            }
            .take-action li {
                margin: 15px 0;
                line-height: 1.6;
            }
        </style>
        
        <script>
        document.querySelectorAll('.commit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const needId = this.getAttribute('data-need-id');
                window.location.href = 'mailto:altadenarisingnow@gmail.com?subject=I can help with Need #' + needId;
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Environmental Safety Post-Fire
     * 
     * Usage: [environmental_safety_post_fire]
     */
    public function environmental_safety_shortcode($atts) {
        ob_start();
        ?>
        <div class="environmental-safety-post-fire">
            <h2>🌱 Environmental Safety After Wildfire</h2>
            <p class="intro">Protecting your health and environment during recovery and rebuilding.</p>
            
            <div class="safety-alert">
                <h3>⚠️ Immediate Health Concerns</h3>
                <ul>
                    <li>Ash and soot contain toxic chemicals and heavy metals</li>
                    <li>Smoke damage can persist in structures</li>
                    <li>Contaminated soil and water require testing</li>
                    <li>Air quality may remain poor for weeks</li>
                </ul>
            </div>
            
            <div class="safety-categories">
                <div class="safety-category">
                    <h4>💨 Air Quality</h4>
                    <ul>
                        <li><strong>Monitor:</strong> Check AirNow.gov daily</li>
                        <li><strong>Protect:</strong> Use N95 masks when outdoors</li>
                        <li><strong>Filter:</strong> Use HEPA filters indoors</li>
                        <li><strong>Ventilate:</strong> Open windows only when AQI is good</li>
                    </ul>
                    <a href="https://www.airnow.gov" target="_blank" class="resource-link">Check Air Quality →</a>
                </div>
                
                <div class="safety-category">
                    <h4>💧 Water Safety</h4>
                    <ul>
                        <li><strong>Test:</strong> Test tap water for contaminants</li>
                        <li><strong>Filter:</strong> Use certified water filters</li>
                        <li><strong>Flush:</strong> Flush pipes before first use</li>
                        <li><strong>Monitor:</strong> Watch for discoloration or odor</li>
                    </ul>
                    <a href="#" class="resource-link">Request Water Testing →</a>
                </div>
                
                <div class="safety-category">
                    <h4>🌍 Soil Testing</h4>
                    <ul>
                        <li><strong>Test:</strong> Test soil before gardening</li>
                        <li><strong>Remediate:</strong> Remove contaminated topsoil</li>
                        <li><strong>Amend:</strong> Add clean compost and mulch</li>
                        <li><strong>Protect:</strong> Use raised beds for food growing</li>
                    </ul>
                    <a href="#" class="resource-link">Find Testing Labs →</a>
                </div>
                
                <div class="safety-category">
                    <h4>🧹 Ash & Debris Cleanup</h4>
                    <ul>
                        <li><strong>PPE:</strong> Wear gloves, mask, goggles</li>
                        <li><strong>Wet Down:</strong> Spray ash before sweeping</li>
                        <li><strong>Dispose:</strong> Bag and seal ash properly</li>
                        <li><strong>Professional:</strong> Consider professional cleaning</li>
                    </ul>
                    <a href="#" class="resource-link">Cleanup Guidelines →</a>
                </div>
            </div>
            
            <div class="health-resources">
                <h3>🏥 Health Resources</h3>
                <div class="health-grid">
                    <div class="health-card">
                        <h4>Free Health Screenings</h4>
                        <p>Get checked for smoke inhalation effects</p>
                        <p><strong>Contact:</strong> LA County Public Health</p>
                        <p><strong>Phone:</strong> 1-800-427-8700</p>
                    </div>
                    <div class="health-card">
                        <h4>Mental Health Support</h4>
                        <p>Trauma counseling and grief support</p>
                        <p><strong>Contact:</strong> LA County DMH</p>
                        <p><strong>Phone:</strong> 1-800-854-7771</p>
                    </div>
                    <div class="health-card">
                        <h4>Environmental Consulting</h4>
                        <p>Professional assessment and guidance</p>
                        <p><strong>Contact:</strong> ThrivingRoots</p>
                        <p><strong>Services:</strong> Site assessment, testing coordination</p>
                    </div>
                </div>
            </div>
            
            <div class="safety-checklist">
                <h3>✅ Safety Checklist for Returning Home</h3>
                <form id="safety-checklist-form">
                    <label><input type="checkbox"> Air quality is at safe levels (AQI < 100)</label>
                    <label><input type="checkbox"> Structural inspection completed</label>
                    <label><input type="checkbox"> Utilities checked and approved</label>
                    <label><input type="checkbox"> Ash and debris cleaned professionally</label>
                    <label><input type="checkbox"> Water tested and safe to drink</label>
                    <label><input type="checkbox"> HVAC system cleaned or replaced</label>
                    <label><input type="checkbox"> Smoke damage remediated</label>
                    <label><input type="checkbox"> PPE available for cleanup work</label>
                    <label><input type="checkbox"> Emergency supplies restocked</label>
                    <label><input type="checkbox"> Health screening completed</label>
                </form>
            </div>
        </div>
        
        <style>
            .environmental-safety-post-fire {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .environmental-safety-post-fire h2 {
                color: #2c5f2d;
                text-align: center;
            }
            .intro {
                text-align: center;
                font-size: 1.1em;
                margin-bottom: 30px;
            }
            .safety-alert {
                background: #fff3e0;
                border-left: 5px solid #ff6f00;
                padding: 20px;
                margin: 30px 0;
                border-radius: 5px;
            }
            .safety-alert h3 {
                color: #ff6f00;
                margin-top: 0;
            }
            .safety-categories {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .safety-category {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                border: 2px solid #2c5f2d;
            }
            .safety-category h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .safety-category ul {
                padding-left: 20px;
            }
            .safety-category li {
                margin: 10px 0;
            }
            .resource-link {
                display: inline-block;
                color: #2c5f2d;
                text-decoration: none;
                font-weight: bold;
                margin-top: 10px;
            }
            .resource-link:hover {
                text-decoration: underline;
            }
            .health-resources {
                margin: 40px 0;
            }
            .health-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .health-card {
                background: #e8f5e9;
                padding: 20px;
                border-radius: 8px;
            }
            .health-card h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .health-card p {
                margin: 8px 0;
            }
            .safety-checklist {
                background: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-top: 40px;
            }
            .safety-checklist h3 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .safety-checklist form {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            .safety-checklist label {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 5px;
                cursor: pointer;
            }
            .safety-checklist label:hover {
                background: #e8f5e9;
            }
            .safety-checklist input[type="checkbox"] {
                width: 20px;
                height: 20px;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Seed fire recovery resources
     */
    public static function seed_fire_recovery_resources() {
        $categories = array(
            'Care Management',
            'Essential Needs',
            'Rehousing',
            'Land & Rebuilding',
            'Community',
            'Environmental',
            'Worker Protection',
            'Fire Systems',
        );
        
        foreach ($categories as $category) {
            wp_insert_term($category, 'need_category');
        }
        
        $statuses = array('Unmet', 'In Progress', 'Being Met', 'Met');
        foreach ($statuses as $status) {
            wp_insert_term($status, 'resource_status');
        }
        
        return true;
    }
}

// Initialize the class
new Eaton_Fire_Recovery();
