<?php

namespace AppBundle\Controller;

use Guzzle\Http\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/lanzamientos", name="lanzamientos")
     */
    public function lanzamientosAction(){
        $offset = $this->get('session')->get('offset')?:0;
        $lanzamientos = $this->getInformation('browse/new-releases?offset='.$offset.'&limit=12');
        $this->get('session')->set('offset',$offset+12);
        return $this->render('AppBundle:Default:lanzamientos.html.twig',array(
            'lanzamientos'=>$lanzamientos['albums']['items']
        ));
    }

    /**
     * @Route("/lanzamientos/siguiente", name="lanzamientos_siguiente")
     */
    public function siguienteAction(){
        $offset = $this->get('session')->get('offset')?:0;
        $lanzamientos = $this->getInformation('browse/new-releases?offset='.$offset.'&limit=12');
        $this->get('session')->set('offset',$offset+12);
        return $this->render('AppBundle:Default:listar.html.twig',array(
            'lanzamientos'=>$lanzamientos['albums']['items']
        ));
    }

    /**
     * @Route("/artista/{id}", name="artista")
     */
    public function artistaAction($id){
        $this->get('session')->set('offset',0);
        $artist = $this->getInformation("artists/$id");
        $songs = $this->getInformation("artists/$id/top-tracks?country=CO");
        return $this->render('AppBundle:Default:artista.html.twig',array(
            'artist'=>$artist,
            'songs'=>$songs['tracks']
        ));
    }


    private function authenticateSpotify(){
        $client = new Client();
        $response = $client->post('https://accounts.spotify.com/api/token',array(
            "Authorization"=>"Basic ".base64_encode("0e7e1f669c404df4b3a491dbe3d0384c:f9e612589f474344bed0452018090193")
        ),array(
            "grant_type"=>"client_credentials"
        ))->send();
        $this->get('session')->set('auth',json_decode($response->getBody(true),true));
    }

    private function getInformation($type){
        $this->authenticateSpotify();
        $client = new Client();
        $auth = $this->get('session')->get('auth');
        $response = $client->get('https://api.spotify.com/v1/'.$type,array(
            'Authorization'=>$auth['token_type']." ".$auth['access_token']
        ))->send();
        return json_decode($response->getBody(true),true);
    }
}
