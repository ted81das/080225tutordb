/**
 * MCP Stats Auto-Updater with Animation
 * Periodically checks for execution count updates and animates changes
 */

class MCPStatsUpdater {
    constructor(options = {}) {
        this.updateInterval = options.updateInterval || 30000; // 30 seconds default
        this.animationDuration = options.animationDuration || 600; // 600ms animation
        this.serverId = options.serverId || null;
        this.previousStats = new Map();
        this.intervalId = null;
        this.isUpdating = false;
        
        // Initialize with current values
        this.initializePreviousStats();
        
        // Start periodic updates
        this.startPeriodicUpdates();
        
        console.log('MCP Stats Updater initialized');
    }
    
    /**
     * Initialize the previous stats from current DOM
     */
    initializePreviousStats() {
        const toolCards = document.querySelectorAll('.tool-card[data-tool-id]');
        
        toolCards.forEach(card => {
            const toolId = card.getAttribute('data-tool-id');
            const countElement = card.querySelector('.tool-execution-count .count-number');
            
            if (countElement && toolId) {
                const currentCount = parseInt(countElement.textContent.replace(/,/g, '')) || 0;
                this.previousStats.set(toolId, currentCount);
            }
        });
        
        console.log('Initialized stats for', this.previousStats.size, 'tools');
    }
    
    /**
     * Start periodic updates
     */
    startPeriodicUpdates() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
        
        this.intervalId = setInterval(() => {
            this.checkForUpdates();
        }, this.updateInterval);
        
        console.log('Started periodic updates every', this.updateInterval / 1000, 'seconds');
    }
    
    /**
     * Stop periodic updates
     */
    stopPeriodicUpdates() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            console.log('Stopped periodic updates');
        }
    }
    
    /**
     * Check for stats updates via AJAX
     */
    async checkForUpdates() {
        if (this.isUpdating) {
            return; // Prevent overlapping requests
        }
        
        this.isUpdating = true;
        
        try {
            const response = await this.fetchLatestStats();
            if (response && response.success) {
                this.processStatsUpdate(response.data);
            }
        } catch (error) {
            console.error('Error fetching stats update:', error);
        } finally {
            this.isUpdating = false;
        }
    }
    
    /**
     * Fetch latest stats from server
     */
    async fetchLatestStats() {
        const formData = new FormData();
        formData.append('action', 'flowmattic_mcp_get_stats_update');
        formData.append('workflow_nonce', FMConfig.workflow_nonce);
        
        if (this.serverId) {
            formData.append('server_id', this.serverId);
        }
        
        const response = await fetch(ajaxurl, {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    }
    
    /**
     * Process the stats update and animate changes
     */
    processStatsUpdate(statsData) {
        let updatedCount = 0;
        
        Object.entries(statsData).forEach(([toolId, newStats]) => {
            const previousCount = this.previousStats.get(toolId) || 0;
            const newCount = newStats.total_executions || 0;
            
            if (previousCount !== newCount) {
                this.animateStatsUpdate(toolId, previousCount, newCount, newStats);
                this.previousStats.set(toolId, newCount);
                updatedCount++;
            }
        });
        
        if (updatedCount > 0) {
            console.log(`Updated stats for ${updatedCount} tools`);
            
            // Optional: Show a subtle notification
            this.showUpdateNotification(updatedCount);
        }
    }
    
    /**
     * Animate the stats update for a specific tool
     */
    animateStatsUpdate(toolId, oldCount, newCount, fullStats) {
        const toolCard = document.querySelector(`[data-tool-id="${toolId}"]`);
        if (!toolCard) return;
        
        const countElement = toolCard.querySelector('.tool-execution-count .count-number');
        const countContainer = toolCard.querySelector('.tool-execution-count');
        
        if (!countElement || !countContainer) return;
        
        // Add updated class for animation
        countContainer.classList.add('updated');
        
        // Update the count with animation
        this.animateNumberChange(countElement, oldCount, newCount);
        
        // Update other stats if they exist
        this.updateToolStats(toolCard, fullStats);
        
        // Remove updated class after animation
        setTimeout(() => {
            countContainer.classList.remove('updated');
        }, this.animationDuration);
        
        // Update no-executions class if needed
        if (oldCount === 0 && newCount > 0) {
            countContainer.classList.remove('no-executions');
        } else if (newCount === 0 && oldCount > 0) {
            countContainer.classList.add('no-executions');
        }
    }
    
    /**
     * Animate number change with counting effect
     */
    animateNumberChange(element, startValue, endValue) {
        const duration = 500; // Animation duration in ms
        const startTime = performance.now();
        const difference = endValue - startValue;
        
        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.round(startValue + (difference * easeOut));
            
            element.textContent = this.formatNumber(currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        };
        
        requestAnimationFrame(updateNumber);
    }
    
    /**
     * Update additional tool stats (success rate, last used, etc.)
     */
    updateToolStats(toolCard, stats) {
        // Update success rate
        const successRateElement = toolCard.querySelector('.stat-value.success-rate-high, .stat-value.success-rate-medium, .stat-value.success-rate-low');
        if (successRateElement && stats.success_rate !== undefined) {
            successRateElement.textContent = stats.success_rate + '%';
            
            // Update success rate class
            successRateElement.className = successRateElement.className.replace(
                /success-rate-(high|medium|low)/,
                `success-rate-${stats.success_rate >= 90 ? 'high' : stats.success_rate >= 70 ? 'medium' : 'low'}`
            );
        }
        
        // Update last execution time
        const lastExecElement = toolCard.querySelector('.stat-value.last-execution');
        if (lastExecElement && stats.last_execution_ago) {
            lastExecElement.textContent = stats.last_execution_ago;
        }
        
        // Update tooltip
        const countContainer = toolCard.querySelector('.tool-execution-count');
        if (countContainer && stats.total_executions !== undefined) {
            countContainer.setAttribute('title', 
                `Total: ${this.formatNumber(stats.total_executions)} executions, Success rate: ${stats.success_rate}%`
            );
        }
    }
    
    /**
     * Format number with commas
     */
    formatNumber(num) {
        return num.toLocaleString();
    }
    
    /**
     * Show a subtle update notification
     */
    showUpdateNotification(count) {
        // Only show if there's a notification system available
        if (typeof showNotification === 'function') {
            showNotification(`Updated stats for ${count} tool${count > 1 ? 's' : ''}`, 'info');
        }
    }
    
    /**
     * Manually trigger an update check
     */
    forceUpdate() {
        console.log('Forcing stats update...');
        this.checkForUpdates();
    }
    
    /**
     * Update the update interval
     */
    setUpdateInterval(newInterval) {
        this.updateInterval = newInterval;
        this.startPeriodicUpdates(); // Restart with new interval
        console.log('Update interval changed to', newInterval / 1000, 'seconds');
    }
    
    /**
     * Destroy the updater
     */
    destroy() {
        this.stopPeriodicUpdates();
        this.previousStats.clear();
        console.log('MCP Stats Updater destroyed');
    }
}

// CSS for the animation (add this to your CSS)
const animationCSS = `
.tool-execution-count.updated {
    animation: countUpdate 0.6s ease-in-out;
}

@keyframes countUpdate {
    0% { 
        transform: scale(1); 
    }
    30% { 
        transform: scale(1.1); 
        background-color: rgba(34, 197, 94, 0.3);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    }
    100% { 
        transform: scale(1); 
    }
}

.tool-execution-count {
    transition: all 0.3s ease;
}
`;

// Inject the CSS
if (!document.querySelector('#mcp-stats-animation-css')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'mcp-stats-animation-css';
    styleSheet.textContent = animationCSS;
    document.head.appendChild(styleSheet);
}

// Initialize the stats updater when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with server ID if available
    const serverId = window.mcpServerId;

    window.mcpStatsUpdater = new MCPStatsUpdater({
        updateInterval: 30000, // 30 seconds
        animationDuration: 600, // 600ms
        serverId: serverId
    });
    
    // Add manual refresh button functionality if it exists
    const refreshButton = document.querySelector('#manual-refresh-stats');
    if (refreshButton) {
        refreshButton.addEventListener('click', () => {
            window.mcpStatsUpdater.forceUpdate();
        });
    }
});

// Cleanup when leaving the page
window.addEventListener('beforeunload', function() {
    if (window.mcpStatsUpdater) {
        window.mcpStatsUpdater.destroy();
    }
});