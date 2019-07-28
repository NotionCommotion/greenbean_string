$(function() {
    $('.manual').click(function(e) {
        $('#dialog-help').dialog('open');
    });

    $("#dialog-help").dialog({
        autoOpen    : false,
        height      : 800,
        width       : 1000,
        open: function(event, ui){
            $(this).find('input.searchHelp').val('').blur();
            helpHistory.reset();
            helpHistory.navigate(1);
            helpHistory.updateHelp(1);
        }
    });
    $("#dialog-help img.back").click(function(){
        if(helpHistory.isBackPossible()){helpHistory.updateHelp(helpHistory.back())}
    });
    $("#dialog-help img.forward").click(function(){
        if(helpHistory.isForwardPossible()){helpHistory.updateHelp(helpHistory.forward())}
    });
    $("#dialog-help img.home").click(function(){
        helpHistory.navigate(1);
        helpHistory.updateHelp(1);
    });
    $("#dialog-help img.print").printIt({elems:'#dialog-help'});
    $("#dialog-help input.searchHelp").autocomplete({source: "/api/manual", minLength: 3,select: function(event, ui){
        $(this).val('').blur();
        helpHistory.updateHelp(ui.item.id);
        return false;   //Important!
    }});
    $("#dialog-help").on("click", "ul.list li", function(){
        var id=$(this).data('id');
        helpHistory.navigate(id);
        helpHistory.updateHelp(id);
    });
    $("#dialog-help").on("click", "p.tree span, span.link", function(){
        var id=$(this).data('id');
        helpHistory.navigate(id);
        helpHistory.updateHelp(id);
    });

    function helpHistory() {
        //Constructor
        this.pages = [ ];
        this.currentPage = -1;
    }
    helpHistory.prototype={
        reset:function() {
            this.pages = [ ];
            this.currentPage = -1;
            //console.log('reset',this.currentPage,this.pages);
        },
        navigate:function(page) {
            this.pages.splice(this.currentPage + 1);
            this.pages.push(page);
            this.currentPage += 1;
            //console.log('navigate',this.currentPage,this.pages);
        },
        back:function() {
            this.currentPage -= 1;
            //console.log('back',this.currentPage,this.pages);
            return this.pages[this.currentPage];
        },
        forward:function() {
            this.currentPage += 1;
            //console.log('forward',this.currentPage,this.pages);
            return this.pages[this.currentPage];
        },
        isBackPossible:function() {
            //console.log('isBackPossible',this.currentPage,this.pages);
            return this.currentPage>0?true:false;
        },
        isForwardPossible:function() {
            //console.log('isForwardPossible',this.currentPage,this.pages);
            return this.currentPage<this.pages.length-1?true:false;
        },
        updateHelp:function(id){
            var dialog=$("#dialog-help"),
            name=dialog.find('p.name').empty(),
            content=dialog.find('div.content').empty(),
            list=dialog.find('ul.list').empty(),
            tree=dialog.find('p.tree').empty();
            $.getJSON('/api/manual/'+id, function (data){
                console.log(data);
                name.text(data.name);
                content.html(data.content);
                for (var i = 0; i < data.topics.length; i++) {
                    if(data.topics[i].display_list) {list.append('<li data-id="'+data.topics[i].id+'">'+data.topics[i].name+'</li>');}
                }
                var _tree='<span data-id=1>Help</span>';
                for (var i = data.tree.length - 1; i >= 0; i--) {
                    //console.log(data.tree.length,i);
                    _tree+=' -> '+((i==0)?data.tree[i].name:'<span data-id='+data.tree[i].id+'>'+data.tree[i].name+'</span>');
                }
                tree.html(_tree);
                }
            );
        }
    }
    var helpHistory=new helpHistory();

    $('.goBack').click(function(){history.back();return false;});

});