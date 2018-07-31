<?php

namespace AppBundle\Controller;

use Guzzle\Http\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $this->authenticateSpotify();
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/lanzamientos", name="lanzamientos")
     */
    public function lanzamientosAction(){
        $this->get('session')->set('offset',0);
        $lanzamientos = $this->getInformation('browse/new-releases');
//        dump($lanzamientos);die;
        return $this->render('AppBundle:Default:lanzamientos.html.twig',array(
            'lanzamientos'=>$lanzamientos['albums']['items']
        ));
    }

    /**
     * @Route("/lanzamientos/siguiente", name="lanzamientos_siguiente")
     */
    public function siguienteAction(){
        $lanzamientos = $this->getInformation('browse/new-releases');
//        dump($lanzamientos);die;
        return $this->render('AppBundle:Default:listar.html.twig',array(
            'lanzamientos'=>$lanzamientos['albums']['items']
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
        $client = new Client();
        $offset = $this->get('session')->get('offset')?:0;
        $auth = $this->get('session')->get('auth');
        $response = $client->get('https://api.spotify.com/v1/'.$type.'?offset='.$offset.'&limit=12',array(
            'Authorization'=>$auth['token_type']." ".$auth['access_token']
        ))->send();
        $this->get('session')->set('offset',$offset+20);
        return json_decode($response->getBody(true),true);
    }
}
