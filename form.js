var Postheadincludes={};
(function($) {
    Postheadincludes={

        table: null,

        template: null,

        addItem: function() {
            $('.postheadincludes.items tr.empty').hide();

            this.table.append(this.createNewItem());

            return this;
        },

        createNewItem: function() {
            var newItem=this.template.clone(true),
                newItemId=this.newItemId();
            
            // No longer a template
            newItem.removeClass('template-item').addClass('item');
            newItem.find('.template-bulk-action').removeClass('template-bulk-action').addClass('bulk-action');
            
            // Rename all inputs
            newItem.find('[data-name]').each(function() {
                var $this=$(this),
                    id=newItemId+'_'+$this.attr('data-name');
                    
                $this.attr('id',id);
                $this.attr('name','postheadincludes['+id+']');
                
                if($this.attr('data-name')=='handle')
                    $this.val(id);
            });
            
            return newItem;
        },

        initialize: function() {
            this.table=$('.postheadincludes.items > tbody');
            this.template=$('.postheadincludes.template-item');
            
            this.initControls();

            return this;
        },
        
        initControls: function() {
            var self=this;
            
            $('[data-action="postheadincludes-additem"]').bind('click',function() {
                self.addItem();
            });
            
            $('[data-action="postheadincludes-removeitems"]').bind('click',function() {
                self.removeSelectedItems($(this));
            });
            
            $('[data-action="postheadincludes-selectall"]').bind('change',function() {
                self.toggleBulkSelection($(this));
            });
            
            // Keep until all browsers support collapsible <summary> tags natively
            $('[data-action="postheadincludes-toggleoptions"]').bind('click',function() {
                self.toggleAdvancedOptions($(this));
            });
            
            return this;
        },
        
        newItemId: function() {
            var newId=(new Date().getTime()).toString().substr(-6);
            return 'item_'+newId;
        },
        
        removeSelectedItems: function(source) {
            var collection=this.table
                .find('.bulk-action')
                .filter(function() {
                    return $(this).attr('checked');
                });
            
            collection.each(function() {
                $(this).closest('tr.item').remove();
            });
            
            source.removeAttr('checked');
            
            if(!this.table.find('tr.item').length)
                $('.postheadincludes.items tr.empty').show();
            
            return this;
        },
        
        toggleAdvancedOptions: function(summary) {
            var details=summary.next();
            if(details.is(':visible'))
                details.hide();
            else
                details.show();
            
            return this;
        },
        
        toggleBulkSelection: function(control) {
            var collection=this.table.find('.bulk-action');
            
            if(control.attr('checked'))
                collection.attr('checked',true);
            else
                collection.removeAttr('checked');
            
            return this;
        }
    
    };
})(jQuery);