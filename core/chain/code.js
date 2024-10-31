function nebulaChain() {

    this.force=function(app,chiave,utente) {
        
        var param={
            "app":app,
            "chiave":chiave,
            "utente":utente
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chain/force.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaChain.postExecute();

            }
        });
        
    }

    this.sblock=function(app,chiave) {
        
        var param={
            "app":app,
            "chiave":chiave
        }

        $.ajax({
            "url": 'http://'+location.host+'/nebula/core/chain/sblock.php',
            "async": true,
            "cache": false,
            "data": {"param": param},
            "type": "POST",
            "success": function(ret) {

                window._nebulaChain.postExecute();

            }
        });
        
    }

    this.postExecute=function() {

    }
    

}