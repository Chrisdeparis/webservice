<?php
 
namespace Application\Controllers;

/**
 *
 *
 */
class ViewRecette extends \Library\Controller\Controller {
    
    /**
     *  Méthode __construct()
     *
     *  Constructeur par défaut appelant le constructeur de Library\Controller\Controller
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     *  Méthode getrecettes($params)
     *  Méthode getviewrecettes($params)
     *
     *  Récupèrera un nombre donnée de recettes
     *       
     *  @param      array       $params     [données de requête]
     *  @return     array
     * 
     */



    /**
     * [getAllViewRecettes description]
     * @param  [array] $param [si param contient un index id_cat,
     *                        la method ne renvera que les recettes de cette categorie]
     * @return [json]        [json de recettes]
     */
    public function getAllViewRecettes($param) {    //  obtenir toutes les recettes
        
        //si on cherche a recevoir qu'un les recettes d'une categorie...
       $where = " 1 ORDER BY `titre` ";

        if(isset($param['id_cat']) && !empty($param['id_cat']) ){
            $where = " `id_cat`={$param['id_cat']} ORDER BY `titre` ";
            echo "tri selon la categorie";            
        }
        else if(isset($param['top']) && !empty($param['top']) ){
            $where=" `top`='1' OR  `top`='2' OR  `top`='3'  ORDER BY `top` ";
            echo "tri selon le top";
        }
        else if(isset($param['type']) && !empty($param['type']) ){
            $where = " `type`='{$param['type']}' ORDER BY `titre` ";
            echo "tri selon le type";
        }

        unset($param['method'], $param['id_cat'], $param['top']);

        echo "coucou : ".$where;

        var_dump("getAllViewRecettes");
        $modelViewAllRecette       = new \Application\Models\ViewRecette();
        $viewAllRecettes           = $modelViewAllRecette->convEnTab($modelViewAllRecette->fetchAll($where) );

        if( empty($viewAllRecettes[0]) ){
             $this->message->addError("Aucune Recette");
        }else{

            //var_dump($viewAllRecettes);

            //recupere la note
            $modelNote  = new \Application\Models\Note();

            foreach ($viewAllRecettes as $key => $viewRecette) {
                $notes=$modelNote->convEnTab($modelNote->fetchAll(" `id_recette`={$viewRecette['id_recette']}  ") );
                $somme=0;
                foreach ($notes as $note) {
                    $somme+=$note['value'];
                }
                $moyenne = -1;
                if(count($notes)>0){
                    $moyenne=$somme/count($notes);
                }
                $viewAllRecettes[$key]['noteMoyenne']=$moyenne;

            }


            //recupere les ingredients
            $modelVLI     = new \Application\Models\ViewListIngredients();

            foreach ($viewAllRecettes as $key => $viewRecette) {

                $viewLI       = $modelVLI->convEnTab( $modelVLI->fetchAll(" `id_recette`={$viewRecette['id_recette']}"));
                $viewAllRecettes[$key]['ingredients']=$viewLI;
            }

            //recupere les produits
            $modelVLP     = new \Application\Models\ViewListProduits();

            foreach ($viewAllRecettes as $key => $viewRecette) {

                $viewLP       = $modelVLP->convEnTab( $modelVLP->fetchAll(" `id_recette`={$viewRecette['id_recette']}")) ;
                $viewAllRecettes[$key]['produits'] = $viewLP;
            }

            //recupere les commentaires
            $modelVC     = new \Application\Models\ViewCommentaire();

            $modelNote     = new \Application\Models\Note();
        
           // $this->setMode("brut");
            foreach ($viewAllRecettes as $key => $viewRecette) {

                $viewC       = $modelVC->convEnTab( $modelVC->fetchAll(" `id_recette`={$viewRecette['id_recette']}")) ;
            
                foreach ($viewC as $key2 => $value) {
                    if (isset($param['id_user'] ) ) {  
                        echo "  `id_user`={$param['id_user']} AND  `id_recette`={$viewRecette['id_recette']} ";
                        $note = $modelNote->convEnTab($modelNote->fetchAll("  `id_user`={$param['id_user']} AND  `id_recette`={$viewRecette['id_recette']} "));
                        $note=$note[0];
                        if(!empty($note)){
                            $viewC[$key2]['note']=$note['value'];
                        }else{      //pas de note ou aucune 
                            $viewC[$key2]['note']="rien";
                        }
                    }else{
                        $viewC[$key2]['note']="rien";        //rien demandé
                    }

                }
                $viewAllRecettes[$key]['commentaires'] = $viewC;

                //ajoute la note
                
                //$viewAllRecettes[$key][''] = $viewLP;
            }

        }

        return $this->setApiResult($viewAllRecettes);
    }




    public function getViewRecette($param) {      //  obtenir une recette par son id
        unset($param['method']);
        $param            = (empty($param["id_recette"]))? null : ($param["id_recette"]+0);

        //recupere la recette
        $modelViewRecette = new \Application\Models\ViewRecette();
        $viewRecette      = $modelViewRecette->convEnTab($modelViewRecette->findByPrimary($param));
        $viewRecetteIPC    = $viewRecette[0];
        if( empty($viewRecette[0]) ){
            return $this->setApiResult(false, true, "Aucune recette pour cet id !");
        }


        //recupere la moyenne des notes
        $modelNote = new \Application\Models\Note();
        $notes=$modelNote->convEnTab($modelNote->fetchAll(" `id_recette`=$param  ") );
        $somme=0;
        foreach ($notes as $note) {
            $somme+=$note['value'];
        }
        $moyenne = -1;
        if(count($notes)>0){
            $moyenne=$somme/count($notes);
        }
        $viewRecetteIPC['noteMoyenne']=$moyenne;



        //recupere les ingredients
        $modelVLI     = new \Application\Models\ViewListIngredients();
        $viewLI       = $modelVLI->convEnTab( $modelVLI->fetchAll(" `id_recette`={$viewRecetteIPC['id_recette']}"));

        if( empty($viewLI) ){
            $viewRecetteIPC['ingredients'] = '';
            //return $this->setApiResult($viewRecetteIPC);
        }else{
            //colle les ingredients à la recette
            $viewRecetteIPC['ingredients'] = $viewLI;
        }

        //recupere les produits
        $modelVLP     = new \Application\Models\ViewListProduits();
        $viewLP       = $modelVLI->convEnTab( $modelVLP->fetchAll(" `id_recette`={$viewRecetteIPC['id_recette']}"));

        if( empty($viewLP) ){
            $viewRecetteIPC['produits'] = '';
            //return $this->setApiResult($viewRecetteIPC);
        }else{
            //colle les produits à la recette
            $viewRecetteIPC['produits'] = $viewLP;
        }

        //recupere les commentairess
        $modelVC    = new \Application\Models\ViewCommentaire();
        $viewC       = $modelVC->convEnTab( $modelVC->fetchAll(" `id_recette`={$viewRecetteIPC['id_recette']}"));
        

        if( empty($viewC) ){
            $viewRecetteIPC['commentaires'] = '';
            //return $this->setApiResult($viewRecetteIPC);
        }else{
            //colle les produits à la recette
            $viewRecetteIPC['commentaires'] = $viewC;
        }


        return $this->setApiResult($viewRecetteIPC);
    }

    
}

