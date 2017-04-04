<?php

namespace Mindlahus\SymfonyAssets\Listener;

/**
 * http://www.chlab.ch/blog/archives/webdevelopment/manually-parse-raw-http-data-php
 * https://gist.github.com/jas-/5c3fdc26fedd11cb9fb5
 * https://www.google.co.uk/search?num=100&safe=active&q=parse_raw_http_request&oq=parse_raw_http_request
 */
class HttpPutStreamListener
{
    /**
     * @var bool|string
     */
    protected $input;

    /**
     * HttpPutStreamHelper constructor.
     * @param Request $request
     * @param array $data
     */
    public function __construct(Request $request, array &$data)
    {
        $this->input = $request->getContent();

        $boundary = $this->boundary();

        if (!count($boundary)) {
            return array(
                'request' => $this->parse(),
                'files' => []
            );
        }

        $blocks = $this->split($boundary);

        $data = $this->blocks($blocks);

        return $data;
    }

    /**
     * @return mixed
     */
    private function boundary()
    {
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        return $matches[1];
    }

    /**
     * @return mixed
     */
    private function parse()
    {
        parse_str(urldecode($this->input), $result);
        return $result;
    }

    /**
     * @param $boundary
     * @return array
     */
    private function split($boundary)
    {
        $result = preg_split("/-+$boundary/", $this->input);
        array_pop($result);
        return $result;
    }

    /**
     * @param array $array
     * @return array
     */
    private function blocks(array $array)
    {
        $results = array(
            'request' => array(),
            'files' => array()
        );

        foreach ($array as $key => $value) {
            if (empty($value))
                continue;

            $block = $this->decide($value);

            if (count($block['request']) > 0)
                array_push($results['request'], $block['request']);

            if (count($block['files']) > 0)
                array_push($results['files'], $block['files']);
        }

        return $this->merge($results);
    }

    /**
     * @param $string
     * @return array
     */
    private function decide($string)
    {
        if (strpos($string, 'application/octet-stream') !== FALSE) {
            return array(
                'request' => $this->file($string),
                'files' => array()
            );
        }

        if (strpos($string, 'filename') !== FALSE) {
            return array(
                'request' => array(),
                'files' => $this->file_stream($string)
            );
        }

        return array(
            'request' => $this->post($string),
            'files' => array()
        );
    }

    /**
     * @param $string
     * @return array
     */
    private function file($string)
    {
        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $string, $match);
        return [
            $match[1] => $match[2] ?? ''
        ];
    }

    /**
     * @param $string
     * @return array
     */
    private function file_stream($string)
    {
        $data = array();

        preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);
        preg_match('/Content-Type: (.*)?/', $match[3], $mime);

        $image = preg_replace('/Content-Type: (.*)[^\n\r]/', '', $match[3]);

        $path = sys_get_temp_dir() . '/php' . substr(sha1(rand()), 0, 6);

        $err = file_put_contents($path, trim($image));

        if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp)) {
            $index = $tmp[1];
        } else {
            $index = $match[1];
        }

        $data[$index]['name'][] = $match[2];
        $data[$index]['type'][] = $mime[1];
        $data[$index]['tmp_name'][] = $path;
        $data[$index]['error'][] = ($err === FALSE) ? $err : 0;
        $data[$index]['size'][] = filesize($path);

        return $data;
    }

    /**
     * @param $string
     * @return array
     */
    private function post($string)
    {
        $data = array();

        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match);

        if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp)) {
            $data[$tmp[1]][] = $match[2] ?? '';
        } else {
            $data[$match[1]] = $match[2] ?? '';
        }

        return $data;
    }

    /**
     * @param $array
     * @return array
     */
    private function merge($array)
    {
        $results = array(
            'request' => [],
            'files' => []
        );

        if (count($array['request']) > 0) {
            foreach ($array['request'] as $key => $value) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $results['request'][$k][] = $vv;
                        }
                    } else {
                        $results['request'][$k] = $v;
                    }
                }
            }
        }

        if (count($array['files']) > 0) {
            foreach ($array['files'] as $key => $value) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            if (
                                is_array($vv)
                                && (count($vv) == 1)
                            ) {
                                $results['files'][$k][$kk] = trim($vv[0]);
                            } else {
                                $results['files'][$k][$kk][] = trim($vv[0]);
                            }
                        }
                    } else {
                        $results['files'][$k][$key] = $v;
                    }
                }
            }
        }

        return $results;
    }
}