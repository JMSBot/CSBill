// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function f(){ log.history = log.history || []; log.history.push(arguments); if(this.console) { var args = arguments, newarr; args.callee = args.callee.caller; newarr = [].slice.call(args); if (typeof console.log === 'object') log.apply.call(console.log, console, newarr); else console.log.apply(console, newarr);}};

// make it safe to use console.log always
(function(a){function b(){}for(var c="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),d;!!(d=c.pop());){a[d]=a[d]||b;}})
(function(){try{console.log();return window.console;}catch(a){return (window.console={});}}());


// place any jQuery/helper plugins in here, instead of separate, slower script files.

/*
 * Form Collection
 */
(function($, Backbone, _){
		
	var MasterView = Backbone.View.extend({
		"tagName"	: "div",
		"className"	: "content",
		"icon" : '<i class="icon-plus-sign"></i>',
		"addlink" : _.template('<a href="#" class="add_form_collection_link"><%= icon %> <%= label %></a>'),
		"legend" : _.template('<legend class="collection-heading"><h<%= heading %>><%= label %> <%= counter %></h<%= heading %>></legend>'),
		"counter" : 0,
		"heading" : 2,

		"initialize": function () {
			this.render();
        },
        "render" : function() {
        	
			var $this = this,
				add_link = $(this.createAddLink()).on("click", function(e) { e.preventDefault(); $this.addForm($this, this); });

			this.$el.append(add_link);
			
			if(this.$el.find('fieldset:first').children().length > 0)
			{
				this.$el.find('fieldset:first').prepend($this.legend({"label" : $this.options.label, "counter" : ++$this.counter}));
			}

        	return this;
        },
        "createAddLink" : function(){
        	return this.addlink({"icon" : this.icon, "label" : ExposeTranslation.get('Add') + " " + this.options.label});
        },
        "addForm" : function($this, l) {
	
        	var prototype  = this.$el.attr('data-prototype'),
        		form = $(prototype.replace(/__name__/g, this.$el.children('.control-group').length)),
        		parents = this.$el.parents('.form-collection').length,
        		heading = this.heading + (parents === 2 ? 3 : parents),
        		scripts = new Array();
		    
		    form.find('fieldset:first').prepend(this.legend({"heading" : heading, "label" : this.options.label, "counter" : ++this.counter}));
		    
		    var form = $('<div />').addClass('content').append(form);
		    
		    /*if(typeof form[1] !== undefined)
		   	{
		    	scripts.push($(form[1]).html());
		   	}*/
		    
		    var view = new FormCollectionView({"el" : form}),
		    	el = view.render().el;

		    $(l).before(el);
		    
		    var legend = $(el).find('.form-collection').siblings('legend'),
		    	head = $('<h' + (heading + 1) + ' />').html(legend.children().first().html());
		    
		    legend.html(head);
		    
		    /*for(var i = 0; i < scripts.length; i++)
		    {
		    	$.globalEval(scripts[i]);
		    }*/
		    
		    Loader.call();
		    
		    return view;
        }
	});
	

	var FormCollectionView = Backbone.View.extend({
		
		"template" : _.template('<a class="pull-right remove-form" href="#"><%= icon %> <%=label%></a>'),
		"icon" : '<i class="icon-remove"></i> ',
		
		"render" : function(){
			
			var $this = this,
				template = this.template({"icon" : this.icon, "label" : ExposeTranslation.get('delete')}),
				tmpl = $(template).on("click", function(e){
							e.preventDefault();
							$this.destroy($this);
				});
			
			this.$el.prepend(tmpl);
				
			return this;
		},
		"destroy" : function($this) {

			$this.$el.remove();
			
			return this;
		}
	});
	
	$.fn.formCollection = function(options) {
		var view = new MasterView($.extend({"el" : this}, options));
	
		return view;
	};

})(window.jQuery, Backbone, _);