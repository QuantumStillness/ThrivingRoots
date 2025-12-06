<?php
/**
 * Community Action Tools & Sustainable Living Guide
 * 
 * Provides actionable steps for mindful eating, sustainable living,
 * and community resilience building.
 *
 * @package ThrivingRoots
 * @subpackage CommunityResilience
 */

if (!defined('ABSPATH')) {
    exit;
}

class Community_Action_Tools {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_custom_post_types'));
        add_shortcode('action_plan_builder', array($this, 'action_plan_builder_shortcode'));
        add_shortcode('sustainable_living_guide', array($this, 'sustainable_living_guide_shortcode'));
        add_shortcode('mindful_eating_resources', array($this, 'mindful_eating_resources_shortcode'));
        add_shortcode('community_garden_finder', array($this, 'community_garden_finder_shortcode'));
    }
    
    /**
     * Register custom post types
     */
    public function register_custom_post_types() {
        // Action Plan CPT
        register_post_type('action_plan', array(
            'labels' => array(
                'name' => __('Action Plans', 'thriving-roots'),
                'singular_name' => __('Action Plan', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'custom-fields'),
            'menu_icon' => 'dashicons-list-view',
        ));
        
        // Sustainable Practice CPT
        register_post_type('sustainable_practice', array(
            'labels' => array(
                'name' => __('Sustainable Practices', 'thriving-roots'),
                'singular_name' => __('Sustainable Practice', 'thriving-roots'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-palmtree',
        ));
    }
    
    /**
     * Shortcode: Action Plan Builder
     * 
     * Usage: [action_plan_builder]
     */
    public function action_plan_builder_shortcode($atts) {
        ob_start();
        ?>
        <div class="action-plan-builder">
            <h3>Build Your Personal Action Plan</h3>
            <p>Create a customized plan for sustainable living and community resilience.</p>
            
            <form id="action-plan-form" class="action-form">
                <div class="form-section">
                    <h4>1. Your Goals</h4>
                    <p>What areas do you want to focus on? (Select all that apply)</p>
                    
                    <label><input type="checkbox" name="goals[]" value="water-conservation"> Water Conservation</label>
                    <label><input type="checkbox" name="goals[]" value="energy-efficiency"> Energy Efficiency</label>
                    <label><input type="checkbox" name="goals[]" value="waste-reduction"> Waste Reduction</label>
                    <label><input type="checkbox" name="goals[]" value="mindful-eating"> Mindful Eating</label>
                    <label><input type="checkbox" name="goals[]" value="urban-gardening"> Urban Gardening</label>
                    <label><input type="checkbox" name="goals[]" value="community-engagement"> Community Engagement</label>
                </div>
                
                <div class="form-section">
                    <h4>2. Your Living Situation</h4>
                    <label>
                        <input type="radio" name="living" value="house" required> House with yard
                    </label>
                    <label>
                        <input type="radio" name="living" value="apartment"> Apartment/Condo
                    </label>
                    <label>
                        <input type="radio" name="living" value="shared"> Shared housing
                    </label>
                </div>
                
                <div class="form-section">
                    <h4>3. Time Commitment</h4>
                    <label>
                        <input type="radio" name="time" value="low" required> 1-2 hours per week
                    </label>
                    <label>
                        <input type="radio" name="time" value="medium"> 3-5 hours per week
                    </label>
                    <label>
                        <input type="radio" name="time" value="high"> 5+ hours per week
                    </label>
                </div>
                
                <div class="form-section">
                    <h4>4. Budget</h4>
                    <label>
                        <input type="radio" name="budget" value="low" required> Minimal ($0-50/month)
                    </label>
                    <label>
                        <input type="radio" name="time" value="medium"> Moderate ($50-200/month)
                    </label>
                    <label>
                        <input type="radio" name="budget" value="high"> Flexible ($200+/month)
                    </label>
                </div>
                
                <button type="submit" class="generate-plan-btn">Generate My Action Plan</button>
            </form>
            
            <div id="action-plan-results" style="display:none;">
                <h3>Your Personalized Action Plan</h3>
                <div id="plan-content"></div>
                <button id="save-plan-btn" class="save-btn">Save My Plan</button>
                <button id="print-plan-btn" class="print-btn">Print Plan</button>
            </div>
        </div>
        
        <script>
        document.getElementById('action-plan-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const goals = formData.getAll('goals[]');
            const living = formData.get('living');
            const time = formData.get('time');
            const budget = formData.get('budget');
            
            // Generate personalized action plan
            const plan = generateActionPlan(goals, living, time, budget);
            
            document.getElementById('plan-content').innerHTML = plan;
            document.getElementById('action-plan-results').style.display = 'block';
            document.getElementById('action-plan-form').style.display = 'none';
        });
        
        function generateActionPlan(goals, living, time, budget) {
            let html = '<div class="action-plan-sections">';
            
            goals.forEach(goal => {
                html += '<div class="plan-section">';
                html += '<h4>' + formatGoalTitle(goal) + '</h4>';
                html += getActionSteps(goal, living, time, budget);
                html += '</div>';
            });
            
            html += '</div>';
            return html;
        }
        
        function formatGoalTitle(goal) {
            return goal.split('-').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }
        
        function getActionSteps(goal, living, time, budget) {
            const steps = {
                'water-conservation': [
                    'Install low-flow showerheads (free from LADWP)',
                    'Fix leaky faucets and toilets',
                    'Take shorter showers (5 minutes or less)',
                    'Turn off water while brushing teeth',
                    'Collect shower warm-up water for plants',
                    'Apply for LADWP Turf Replacement rebate',
                ],
                'energy-efficiency': [
                    'Switch to LED light bulbs',
                    'Unplug devices when not in use',
                    'Use power strips to eliminate phantom energy',
                    'Set thermostat to 78°F in summer, 68°F in winter',
                    'Apply for LADWP solar incentive program',
                    'Seal air leaks around windows and doors',
                ],
                'waste-reduction': [
                    'Start composting food scraps',
                    'Use reusable bags, bottles, and containers',
                    'Buy in bulk to reduce packaging',
                    'Repair items instead of replacing',
                    'Donate or sell unwanted items',
                    'Attend LASAN recycling workshops',
                ],
                'mindful-eating': [
                    'Buy local and seasonal produce',
                    'Reduce meat consumption',
                    'Grow your own herbs and vegetables',
                    'Plan meals to reduce food waste',
                    'Support farmers markets',
                    'Learn about food sourcing and sustainability',
                ],
                'urban-gardening': [
                    'Start a container garden on your balcony',
                    'Join a community garden',
                    'Get free trees from City Plants',
                    'Attend LADWP landscaping classes',
                    'Create a pollinator-friendly garden',
                    'Practice water-wise gardening',
                ],
                'community-engagement': [
                    'Join local environmental groups',
                    'Attend city council meetings',
                    'Volunteer for community cleanups',
                    'Share resources with neighbors',
                    'Organize neighborhood sustainability events',
                    'Mentor others in sustainable practices',
                ],
            };
            
            const goalSteps = steps[goal] || [];
            let html = '<ol class="action-steps">';
            
            goalSteps.forEach(step => {
                html += '<li>' + step + '</li>';
            });
            
            html += '</ol>';
            html += '<p class="timeline"><strong>Timeline:</strong> Start with 2-3 actions this month</p>';
            
            return html;
        }
        
        document.getElementById('save-plan-btn').addEventListener('click', function() {
            const plan = document.getElementById('plan-content').innerHTML;
            localStorage.setItem('myActionPlan', plan);
            alert('Your action plan has been saved!');
        });
        
        document.getElementById('print-plan-btn').addEventListener('click', function() {
            window.print();
        });
        </script>
        
        <style>
            .action-plan-builder {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .action-form {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 10px;
            }
            .form-section {
                margin-bottom: 30px;
            }
            .form-section h4 {
                color: #2c5f2d;
                margin-bottom: 15px;
            }
            .form-section label {
                display: block;
                margin: 10px 0;
                padding: 10px;
                background: #fff;
                border-radius: 5px;
                cursor: pointer;
            }
            .form-section label:hover {
                background: #e8f5e9;
            }
            .form-section input[type="checkbox"],
            .form-section input[type="radio"] {
                margin-right: 10px;
            }
            .generate-plan-btn, .save-btn, .print-btn {
                background: #2c5f2d;
                color: #fff;
                padding: 15px 30px;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                margin: 10px 5px;
            }
            .generate-plan-btn:hover, .save-btn:hover, .print-btn:hover {
                background: #1e4620;
            }
            .action-plan-sections {
                margin-top: 20px;
            }
            .plan-section {
                background: #fff;
                padding: 20px;
                margin-bottom: 20px;
                border-left: 4px solid #2c5f2d;
                border-radius: 5px;
            }
            .plan-section h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .action-steps {
                padding-left: 20px;
            }
            .action-steps li {
                margin: 10px 0;
                line-height: 1.6;
            }
            .timeline {
                margin-top: 15px;
                padding: 10px;
                background: #e8f5e9;
                border-radius: 5px;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Sustainable Living Guide
     * 
     * Usage: [sustainable_living_guide]
     */
    public function sustainable_living_guide_shortcode($atts) {
        ob_start();
        ?>
        <div class="sustainable-living-guide">
            <h3>Sustainable Living Guide</h3>
            <p>Practical tips for reducing your environmental impact and building community resilience.</p>
            
            <div class="guide-categories">
                <div class="guide-category">
                    <h4>🚰 Water Conservation</h4>
                    <ul>
                        <li><strong>Indoor:</strong> Fix leaks, install low-flow fixtures, take shorter showers</li>
                        <li><strong>Outdoor:</strong> Water early morning or evening, use drip irrigation, mulch gardens</li>
                        <li><strong>Rebates:</strong> Apply for LADWP Turf Replacement ($3/sq ft)</li>
                        <li><strong>Goal:</strong> Reduce water use by 20% in 6 months</li>
                    </ul>
                </div>
                
                <div class="guide-category">
                    <h4>⚡ Energy Efficiency</h4>
                    <ul>
                        <li><strong>Lighting:</strong> Switch to LED bulbs (75% less energy)</li>
                        <li><strong>Appliances:</strong> Unplug when not in use, use Energy Star rated</li>
                        <li><strong>Heating/Cooling:</strong> Seal air leaks, use programmable thermostat</li>
                        <li><strong>Solar:</strong> Consider LADWP Solar Incentive Program</li>
                    </ul>
                </div>
                
                <div class="guide-category">
                    <h4>♻️ Waste Reduction</h4>
                    <ul>
                        <li><strong>Reduce:</strong> Buy only what you need, choose minimal packaging</li>
                        <li><strong>Reuse:</strong> Repair items, donate unwanted goods, use reusables</li>
                        <li><strong>Recycle:</strong> Learn what can be recycled in LA</li>
                        <li><strong>Compost:</strong> Turn food scraps into garden soil</li>
                    </ul>
                </div>
                
                <div class="guide-category">
                    <h4>🌱 Urban Gardening</h4>
                    <ul>
                        <li><strong>Start Small:</strong> Herbs on windowsill, tomatoes in containers</li>
                        <li><strong>Community Gardens:</strong> Join or start a neighborhood garden</li>
                        <li><strong>Free Trees:</strong> Get up to 7 free trees from City Plants</li>
                        <li><strong>Native Plants:</strong> Choose California Friendly® species</li>
                    </ul>
                </div>
                
                <div class="guide-category">
                    <h4>🍎 Mindful Eating</h4>
                    <ul>
                        <li><strong>Local:</strong> Shop at farmers markets, support local farms</li>
                        <li><strong>Seasonal:</strong> Eat what's in season (fresher, cheaper)</li>
                        <li><strong>Plant-Based:</strong> Reduce meat consumption for health & planet</li>
                        <li><strong>Food Waste:</strong> Plan meals, use leftovers, compost scraps</li>
                    </ul>
                </div>
                
                <div class="guide-category">
                    <h4>🤝 Community Engagement</h4>
                    <ul>
                        <li><strong>Share Resources:</strong> Tool libraries, seed swaps, skill sharing</li>
                        <li><strong>Organize:</strong> Neighborhood cleanups, sustainability events</li>
                        <li><strong>Advocate:</strong> Attend city meetings, support green policies</li>
                        <li><strong>Educate:</strong> Share knowledge with neighbors and friends</li>
                    </ul>
                </div>
            </div>
            
            <div class="quick-wins">
                <h4>🎯 Quick Wins (Start Today!)</h4>
                <div class="quick-wins-grid">
                    <div class="quick-win-card">
                        <h5>5 Minutes</h5>
                        <p>Turn off lights when leaving a room</p>
                        <p>Unplug chargers not in use</p>
                        <p>Turn off water while brushing teeth</p>
                    </div>
                    <div class="quick-win-card">
                        <h5>30 Minutes</h5>
                        <p>Fix a leaky faucet</p>
                        <p>Start a compost bin</p>
                        <p>Plan this week's meals</p>
                    </div>
                    <div class="quick-win-card">
                        <h5>This Weekend</h5>
                        <p>Visit a farmers market</p>
                        <p>Apply for LADWP rebates</p>
                        <p>Join a community garden</p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .sustainable-living-guide {
                max-width: 1000px;
                margin: 0 auto;
            }
            .guide-categories {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .guide-category {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                border: 2px solid #2c5f2d;
            }
            .guide-category h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .guide-category ul {
                padding-left: 20px;
            }
            .guide-category li {
                margin: 10px 0;
                line-height: 1.6;
            }
            .quick-wins {
                background: #e8f5e9;
                padding: 30px;
                border-radius: 10px;
                margin-top: 30px;
            }
            .quick-wins h4 {
                color: #2c5f2d;
                text-align: center;
                margin-top: 0;
            }
            .quick-wins-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .quick-win-card {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }
            .quick-win-card h5 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .quick-win-card p {
                margin: 10px 0;
                font-size: 0.9em;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Mindful Eating Resources
     * 
     * Usage: [mindful_eating_resources]
     */
    public function mindful_eating_resources_shortcode($atts) {
        ob_start();
        ?>
        <div class="mindful-eating-resources">
            <h3>Mindful Eating & Material Safety</h3>
            <p>Make informed choices about food sourcing, safety, and sustainability.</p>
            
            <div class="eating-sections">
                <div class="eating-section">
                    <h4>🥬 Local Food Sources</h4>
                    <ul>
                        <li><strong>Farmers Markets:</strong> Fresh, local, seasonal produce</li>
                        <li><strong>CSA Programs:</strong> Community Supported Agriculture boxes</li>
                        <li><strong>Urban Farms:</strong> Support local urban agriculture</li>
                        <li><strong>Food Co-ops:</strong> Member-owned grocery stores</li>
                    </ul>
                    <a href="#" class="resource-btn">Find Farmers Markets Near You</a>
                </div>
                
                <div class="eating-section">
                    <h4>🔬 Food Safety Resources</h4>
                    <ul>
                        <li><strong>EWG's Dirty Dozen:</strong> Produce with most pesticides</li>
                        <li><strong>EWG's Clean Fifteen:</strong> Produce with least pesticides</li>
                        <li><strong>Food Labels:</strong> Understanding organic, non-GMO, etc.</li>
                        <li><strong>Water Quality:</strong> Check your tap water safety</li>
                    </ul>
                    <a href="#" class="resource-btn">Check Water Quality</a>
                </div>
                
                <div class="eating-section">
                    <h4>🌍 Environmental Impact</h4>
                    <ul>
                        <li><strong>Carbon Footprint:</strong> Choose low-impact foods</li>
                        <li><strong>Water Usage:</strong> Some foods use more water than others</li>
                        <li><strong>Packaging:</strong> Reduce single-use plastics</li>
                        <li><strong>Food Miles:</strong> Buy local to reduce transportation</li>
                    </ul>
                </div>
                
                <div class="eating-section">
                    <h4>💡 Practical Tips</h4>
                    <ul>
                        <li><strong>Meal Planning:</strong> Reduce waste, save money</li>
                        <li><strong>Batch Cooking:</strong> Save time and energy</li>
                        <li><strong>Food Storage:</strong> Keep produce fresh longer</li>
                        <li><strong>Composting:</strong> Turn scraps into garden gold</li>
                    </ul>
                </div>
            </div>
            
            <div class="food-safety-checker">
                <h4>🔍 Quick Food Safety Checker</h4>
                <form id="food-safety-form">
                    <label for="food-item">Enter a food item:</label>
                    <input type="text" id="food-item" placeholder="e.g., strawberries, spinach">
                    <button type="submit">Check Safety Info</button>
                </form>
                <div id="food-safety-results"></div>
            </div>
        </div>
        
        <style>
            .mindful-eating-resources {
                max-width: 1000px;
                margin: 0 auto;
            }
            .eating-sections {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .eating-section {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                border-left: 4px solid #2c5f2d;
            }
            .eating-section h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .eating-section ul {
                padding-left: 20px;
            }
            .eating-section li {
                margin: 10px 0;
                line-height: 1.6;
            }
            .resource-btn {
                display: inline-block;
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 10px;
            }
            .resource-btn:hover {
                background: #1e4620;
            }
            .food-safety-checker {
                background: #e8f5e9;
                padding: 30px;
                border-radius: 10px;
                margin-top: 30px;
            }
            .food-safety-checker h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .food-safety-checker form {
                display: flex;
                gap: 10px;
                margin-top: 20px;
            }
            .food-safety-checker input {
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .food-safety-checker button {
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Community Garden Finder
     * 
     * Usage: [community_garden_finder zip="90001"]
     */
    public function community_garden_finder_shortcode($atts) {
        $atts = shortcode_atts(array(
            'zip' => '',
        ), $atts);
        
        ob_start();
        ?>
        <div class="community-garden-finder">
            <h3>Find Community Gardens Near You</h3>
            <p>Connect with local gardens, urban farms, and growing spaces.</p>
            
            <form id="garden-finder-form">
                <label for="zip-input">Enter your ZIP code:</label>
                <input type="text" id="zip-input" name="zip" maxlength="5" pattern="[0-9]{5}" value="<?php echo esc_attr($atts['zip']); ?>" required>
                <button type="submit">Find Gardens</button>
            </form>
            
            <div id="garden-results">
                <div class="garden-card">
                    <h4>Sample Community Garden</h4>
                    <p class="garden-address">123 Green St, Los Angeles, CA 90001</p>
                    <p>Open community garden with plots available for rent. Organic practices, tool sharing, and monthly workshops.</p>
                    <div class="garden-details">
                        <p><strong>Plot Size:</strong> 10x10 ft</p>
                        <p><strong>Cost:</strong> $50/year</p>
                        <p><strong>Amenities:</strong> Water, tools, compost</p>
                        <p><strong>Contact:</strong> (213) 555-0123</p>
                    </div>
                    <a href="#" class="garden-btn">Request Plot</a>
                </div>
            </div>
        </div>
        
        <style>
            .community-garden-finder {
                max-width: 800px;
                margin: 0 auto;
            }
            #garden-finder-form {
                display: flex;
                gap: 10px;
                margin: 20px 0;
            }
            #garden-finder-form input {
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            #garden-finder-form button {
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .garden-card {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                border: 2px solid #2c5f2d;
                margin: 20px 0;
            }
            .garden-card h4 {
                color: #2c5f2d;
                margin-top: 0;
            }
            .garden-address {
                color: #666;
                font-style: italic;
                margin: 5px 0 15px 0;
            }
            .garden-details {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
            }
            .garden-details p {
                margin: 5px 0;
            }
            .garden-btn {
                display: inline-block;
                background: #2c5f2d;
                color: #fff;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
            }
            .garden-btn:hover {
                background: #1e4620;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new Community_Action_Tools();
