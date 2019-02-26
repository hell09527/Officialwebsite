<?php

namespace App\Controller;


use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;



class DefaultController extends AbstractController
{

    public function index(Request $request,LoggerInterface $logger)
    {

        $env =  getenv('APP_ENV');
        $lang = $request->request->get('lang', 'jp');
        $lang = strtolower($lang);
        if($lang !== 'jp') {
            return $this->json(['code'=> 404, 'message'=> 'invalid parameters']);
        }

        $message_ = $request->request->get('message', 'asdfasd');

        $logger->info('message:'. var_export($message_,true));
        $message_str = base64_decode($message_);
#        $message_str = urldecode($message_str);

        // random:md5hash
        $sign = $request->headers->get('X-SIGN');

        $logger->info('sign:'. $sign);
        $random = substr($sign, 0,8);
        $hash0 = substr($sign, 8,32);

        $logger->info('random:'. $random);
        $logger->info('hash0:'. $hash0);

        $logger->info('message json:'.$message_str);
        $logger->info('lang:'.$lang);

        $logger->info('to md5(message+ lang+ random):'.$message_str. $lang.$random);

        $hash1 = md5($message_str. $lang.$random);

        $logger->info('hash1:'. $hash1);
        if($hash1 !== $hash0) {
            return $this->json(['code'=> 403, 'message'=> 'invalid sign']);
        }

        parse_str($message_str,$messages);

        $host = $request->headers->get('host');
        $content_type = $request->headers->get('content-type');

        $logger->info('host:'. $host);
        $logger->info('content_type:'. $content_type);
        $conf = array(
            'host' => getenv('SMTP_HOST') ,
            'port' => getenv('SMTP_PORT'),
            'sasl_username' => getenv('SMTP_USER'),
            'sasl_password' => getenv('SMTP_PASS'),

        );
        // Create the Transport
        $transport = (new \Swift_SmtpTransport($conf['host'], $conf['port'],'ssl'))
            ->setUsername($conf['sasl_username'])
            ->setPassword($conf['sasl_password'])
            ;

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);

        $m_ = [];
        foreach( $messages as $k => $v ) {
            $m_ [] = $k . ': ' . $v . "\n";
        } 
        if($env === 'prod') {
            // Create a message
            $message = (new \Swift_Message('New message'. '['.$env.']'))
                ->setFrom(['noreply@ushopal.com'])
                ->setTo(['dentsu@ushopal.com' => 'Dentsu'])
                ->setBcc(['jarod@ushopal.com' => 'Jarod Chianng','huiming.yang@ushopal.com' => 'Yang'])
                ->setBody('Here is the message itself:'. "\n\n". implode('', $m_). ' '. $lang)
                ;
///                ->setBody('Here is the message itself:'. "\n\n". $message_str. ' '. $lang)
        } else {

            // Create a message
            $message = (new \Swift_Message('new message'. '['.$env.']'))
                ->setFrom(['noreply@ushopal.com'])
                ->setTo(['huiming.yang@ushopal.com' => 'Yang'])
                ->setBcc(['jarod@ushopal.com' => 'Jarod Chianng'])
                ->setBody('Here is the message itself:'. "\n\n". implode('', $m_). ' '. $lang)
                ;
        }
            //->setCc(['huiming.yang@ushopal.com'])
//            ->setTo(['jarod@ushopal.com' => 'Jarod Chiang'])

        try {
            $result = $mailer->send($message);
        } catch(\Exception $e) {

            $logger->critical($e->getMessage(). ' . ' . $message);

            return $this->json(['code'=> 500,'result'=> 'internal error']);
        }
        // Send the message

        if($result > 0) {
            return $this->json(['result'=> 'success', 'code'=> 200]);
        }

        return $this->json(['result'=> 'message send failed', 'code'=> 501]);

    }

}
