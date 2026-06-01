/**
 * @commandID rm
 * @nicename Delete
 */
elFinder.prototype.commands.rm = function() {
    this.contextmenu = true;
    // Copy the original command structure
    this.init = function() {
        // Set title, etc.
        this.title = 'Delete';
    };
    
    this.getstate = function() {
        // Return 0 to enable, -1 to disable
        return this.fm.selectedFiles().length ? 0 : -1;
    };
    
    this.exec = function() {
        // Your custom logic
        alert("Hello, world!");
        return $.Deferred().resolve();
    };
};