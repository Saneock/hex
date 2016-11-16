<?php
namespace Hex\Base;

/**
 * Класс веб-ответа, представляющий собой ответ HTTP
 *
 * Он содержит [[headers]], [[cookies]] и [[content]] которые должны быть отправлен клиенту.
 * Он также контролирует HTTP [[statusCode|код ответа]].
 *
 * Ответ сконфигурирован как компонент приложения в [[\Hex\Base\Application]] по умолчанию.
 * Вы можете получить доступ к этому экземпляру через `Application::$response`.
 *
 * @property CookieCollection $cookies Коллекция cookie. Это свойство только для чтения.
 * @property string $downloadHeaders Имя файла вложения. Это свойство только для записи.
 * @property HeaderCollection $headers Коллекция заголовков. Это свойство только для чтения.
 * @property boolean $isClientError Является ли ответ ошибкой клиента. Это свойство только для чтения.
 * @property boolean $isEmpty Является ли ответ пустым. Это свойство только для чтения.
 * @property boolean $isForbidden Является ли запрос запрещённым. Это свойство только для чтения.
 * @property boolean $isInformational Является ли запрос информативным. Это свойство только для чтения.
 * @property boolean $isInvalid Имеет ли запрос верный [[statusCode]]. Это свойство только для чтения.
 * @property boolean $isNotFound Является ли запрос не найденным. Это свойство только для чтения.
 * @property boolean $isOk Является ли запрос нормальным (OK). Это свойство только для чтения.
 * @property boolean $isRedirection Является ли ответ редиректом. Это свойство только для чтения.
 * @property boolean $isServerError Является ли ответ ошибкой сервера. Это свойство только для чтения.
 * @property boolean $isSuccessful Является ли ответ успешным. Это свойство только для чтения.
 * @property integer $statusCode HTTP код ответа
 */
class Response extends \Abstracts\Response
{
    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';

    /**
     * @var string формат ответа. Это определяет, как преобразовать [[data]] в [[content]]
     * Значение этого свойства должно быть одним из ключей, объявленных в [[formatters] массиве.
     * По умолчанию поддерживаются следующие форматы:
     *
     * - [[FORMAT_RAW]]: Данные будут рассматриваться в качестве ответного контента без какого-либо преобразования.
     *   Никаких дополнительных заголовков HTTP не будет добавлено.
     * - [[FORMAT_HTML]]: Данные будут рассматриваться в качестве ответного контента без какого-либо преобразования.
     *   "Content-Type" заголовок будет установлен как "text/html".
     * - [[FORMAT_JSON]]: Данные будут преобразованы в формат JSON. "Content-Type" заголовок будет установлен как "application/json".
     * - [[FORMAT_JSONP]]: Данные будут преобразованы в формат JSONP. "Content-Type" заголовок будет установлен как "text/javascript".
     *   Отметим, что в этом случае `$data` должен быть массивом c "data" и элементами "callback".
     *   Первое относится к данным, которые будут отправлены, в то время как последнее относится к
     *   имени callback функции JavaScript.
     * - [[FORMAT_XML]]: Данные будут преобразованы в формат XML. Пожалуйста, обратитесь к [[XmlResponseFormatter]]
     *   для больших подробностей.
     *
     * Вы можете настроить процесс форматирования или поддержки дополнительных форматов путем настройки [[formatters]].
     * @see formatters
     */
    public $format = self::FORMAT_HTML;
    /**
     * @var string MIME тип (например `application/json`) из ACCEPT заголовока запроса для этого ответа.
     */
    public $acceptMimeType;
    /**
     * @var array Параметры (например `['q' => 1, 'version' => '1.0']`) связаные с [[acceptMimeType|выбранный MIME тип]].
     * Это список пар имя-значение, связанных с [[acceptMimeType]] от ACCEPT HTTP заголовка.
     */
    public $acceptParams = [];
    /**
     * @var array Форматировщики для преобразования данных в содержание ответа указанного [[format]].
     * Ключи массива являются имена формата, а значения массива являются соответствующими конфигурациями
     * для создания объектов форматировщиков.
     *
     * @see format
     * @see defaultFormatters
     */
    public $formatters = [];
    /**
     * @var mixed исходные данные ответа. Когда не является пустой, то она будет преобразована в [[content]]
     * в соответствии с [[format]], когда посылается ответ.
     * @see content
     */
    public $data;
    /**
     * @var string содержание ответа. Когда [[data]] не является пустой, то она будет преобразована в [[content]]
     * в соответствии с [[format]], когда посылается ответ.
     * @see data
     */
    public $content;
    /**
     * @var resource|array поток для отправки. Это может быть поток или массив с потоками,
     * начальное положение и конечное положение. Обратите внимание, что, когда это свойство установлено, то [[data]] и [[content]]
     * cвойства будут проигнорированы [[send()]].
     */
    public $stream;
    /**
     * @var string Кодировка текстового ответа. Если он не установлен, он будет использоваться
     * значение [[Application::charset]].
     */
    public $charset;
    /**
     * @var string Описание состояния HTTP, который поставляется вместе с кодом состояния.
     * @see httpStatuses
     */
    public $statusText = 'OK';
    /**
     * @var версия протокола HTTP для использования. Если он не установлен, то он будет определен с помощью `$ _SERVER [ 'SERVER_PROTOCOL']`, или '1.1', если это не доступно.
     */
    public $version;
    /**
     * @var boolean Был ли послан ответ. Если это правда, вызов [[send()]] ничего не будет делать.
     */
    public $isSent = false;
    /**
     * @var array Список кодов статуса HTTP и соответствующие тексты
     */
    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var integer Код состояния HTTP для отправки с ответом.
     */
    private $_statusCode = 200;
    /**
     * @var HeaderCollection
     */
    private $_headers;


    /**
     * Инициализирует этот компонент.
     */
    public function init()
    {
        if ($this->version === null) {
            if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
                $this->version = '1.0';
            } else {
                $this->version = '1.1';
            }
        }
        if ($this->charset === null) {
            $this->charset = Application::$charset;
        }
        $this->formatters = array_merge($this->defaultFormatters(), $this->formatters);
    }

    /**
     * @return integer Код состояния HTTP для отправки с ответом.
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Устанавливает код состояния ответа.
     * Этот метод установит соответствующий текст состояния, если `$text` не указан.
     *
     * @param integer $value Код состояния
     * @param string $text Текст статуса. Если он не установлен, он будет установлен автоматически на основе кода состояния.
     * @throws \Exception\InvalidParam Если код состояния является недопустимым.
     */
    public function setStatusCode($value, $text = null)
    {
        if ($value === null) {
            $value = 200;
        }
        $this->_statusCode = (int) $value;
        if ($this->getIsInvalid()) {
            throw new \Exception\InvalidParam("The HTTP status code is invalid: $value");
        }
        if ($text === null) {
            $this->statusText = isset(static::$httpStatuses[$this->_statusCode]) ? static::$httpStatuses[$this->_statusCode] : '';
        } else {
            $this->statusText = $text;
        }
    }

    /**
     * Возвращает коллекцию заголовков.
     * Коллекция заголовков содержит текущие HTTP заголовки
     *
     * @return HeaderCollection Коллекция заголовков
     */
    public function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = new HeaderCollection;
        }
        return $this->_headers;
    }

    /**
     * Отправляет ответ клиенту
     */
    public function send()
    {
        if ($this->isSent) {
            return;
        }
        //$this->trigger(self::EVENT_BEFORE_SEND);
        $this->prepare();
        //$this->trigger(self::EVENT_AFTER_PREPARE);
        $this->sendHeaders();
        $this->sendContent();
        //$this->trigger(self::EVENT_AFTER_SEND);
        $this->isSent = true;
    }

    /**
     * Очищает заголовки, cookie, контент, код ответа
     */
    public function clear()
    {
        $this->_headers = null;
        $this->_cookies = null;
        $this->_statusCode = 200;
        $this->statusText = 'OK';
        $this->data = null;
        $this->stream = null;
        $this->content = null;
        $this->isSent = false;
    }

    /**
     * Отправляет заголовки ответа клиенту
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }
        if ($this->_headers) {
            $headers = $this->getHeaders(); 
            foreach ($headers as $name => $values) {
                $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
                // set replace for first occurrence of header but false afterwards to allow multiple
                $replace = true;
                foreach ($values as $value) {
                    header("$name: $value", $replace);
                    $replace = false;
                }
            }
        }
        $statusCode = $this->getStatusCode();
        header("HTTP/{$this->version} {$statusCode} {$this->statusText}");
        $this->sendCookies();
    }

    /**
     * Посылает cookie клиенту
     * @todo Решить попрос с Security
     */
    protected function sendCookies()
    {
        if ($this->_cookies === null) {
            return;
        }
        $request = Application::$request;
        if ($request->enableCookieValidation) {
            if ($request->cookieValidationKey == '') {
                throw new \Exception\InvalidConfig(get_class($request) . '::cookieValidationKey must be configured with a secret key.');
            }
            $validationKey = $request->cookieValidationKey;
        }
        foreach ($this->getCookies() as $cookie) {
            $value = $cookie->value;
            if ($cookie->expire != 1  && isset($validationKey)) {
                $value = Yii::$app->getSecurity()->hashData(serialize([$cookie->name, $value]), $validationKey);
            }
            setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }
    }

    /**
     * Посылает содержимое ответа клиенту
     */
    protected function sendContent()
    {
        if ($this->stream === null) {
            echo $this->content;

            return;
        }

        set_time_limit(0); // Reset time limit for big files
        $chunkSize = 8 * 1024 * 1024; // 8MB per chunk

        if (is_array($this->stream)) {
            list ($handle, $begin, $end) = $this->stream;
            fseek($handle, $begin);
            while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
                if ($pos + $chunkSize > $end) {
                    $chunkSize = $end - $pos + 1;
                }
                echo fread($handle, $chunkSize);
                flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
            }
            fclose($handle);
        } else {
            while (!feof($this->stream)) {
                echo fread($this->stream, $chunkSize);
                flush();
            }
            fclose($this->stream);
        }
    }

    /**
     * Посылает файл в браузер.
     *
     * Следует отметить, что этот метод только готовит ответ для отправки файла. Файл не будет отправлен
     * до вызова [[send()]] явно или неявно. Последнее происходит после возврата действия контроллером.
     *
     * @param string $filePath Путь к файлу, который будет отправлен.
     * @param string $attachmentName Имя файла, который будет показан пользователю.
     * @param array $options дополнительные опции для отправки файла. Поддерживаются следующие опции:
     *
     *  - `mimeType`: MIME тип содержимого. По умолчанию 'application/octet-stream'.
     *  - `inline`: boolean, Должен ли браузер должен открыть файл в окне браузера. По умолчанию false,
     *    что означает, что диалог загрузки появится.
     *
     * @return $this Сам объект ответа
     */
    public function sendFile($filePath, $attachmentName = null, $options = [])
    {
        if (!isset($options['mimeType'])) {
            $options['mimeType'] = FileHelper::getMimeTypeByExtension($filePath);
        }
        if ($attachmentName === null) {
            $attachmentName = basename($filePath);
        }
        $handle = fopen($filePath, 'rb');
        $this->sendStreamAsFile($handle, $attachmentName, $options);

        return $this;
    }

    /**
     * Посылает указанное содержимое в виде файла браузер.
     *
     * Следует отметить, что этот метод только готовит ответ для отправки файла. Файл не будет отправлен
     * до вызова [[send()]] явно или неявно. Последнее происходит после возврата действия контроллером.
     *
     * @param string $content контент для отправки. Существующий [[content]] будет отброшен.
     * @param string $attachmentName Имя файла, который будет показан пользователю.
     * @param array $options дополнительные опции для отправки файла. Поддерживаются следующие опции:
     *
     *  - `mimeType`: MIME тип содержимого. По умолчанию 'application/octet-stream'.
     *  - `inline`: boolean, Должен ли браузер должен открыть файл в окне браузера. По умолчанию false,
     *    что означает, что диалог загрузки появится.
     *
     * @return $this Сам объект ответа
     * @throws \Exceptions\Http Если запрашиваемый диапазон не выполним
     */
    public function sendContentAsFile($content, $attachmentName, $options = [])
    {
        $headers = $this->getHeaders();

        $contentLength = StringHelper::byteLength($content);
        $range = $this->getHttpRange($contentLength);

        if ($range === false) {
            $headers->set('Content-Range', "bytes */$contentLength");
            throw new \Exceptions\Http\Client\RequestedRangeNotSatisfiableException('Requested range not satisfiable');
        }

        list($begin, $end) = $range;
        if ($begin != 0 || $end != $contentLength - 1) {
            $this->setStatusCode(206);
            $headers->set('Content-Range', "bytes $begin-$end/$contentLength");
            $this->content = StringHelper::byteSubstr($content, $begin, $end - $begin + 1);
        } else {
            $this->setStatusCode(200);
            $this->content = $content;
        }

        $mimeType = isset($options['mimeType']) ? $options['mimeType'] : 'application/octet-stream';
        $this->setDownloadHeaders($attachmentName, $mimeType, !empty($options['inline']), $end - $begin + 1);

        $this->format = self::FORMAT_RAW;

        return $this;
    }

    /**
     * Посылает указанный поток в виде файла в браузер.
     *
     * Следует отметить, что этот метод только готовит ответ для отправки файла. Файл не будет отправлен
     * до вызова [[send()]] явно или неявно. Последнее происходит после возврата действия контроллером.
     *
     * @param resource $handle Обработчик потока для отправки
     * @param string $attachmentName Имя файла, который будет показан пользователю.
     * @param array $options дополнительные опции для отправки файла. Поддерживаются следующие опции:
     
     *  - `mimeType`: MIME тип содержимого. По умолчанию 'application/octet-stream'.
     *  - `inline`: boolean, Должен ли браузер должен открыть файл в окне браузера. По умолчанию false,
     *    что означает, что диалог загрузки появится.
     *  - `fileSize`: Размер контента для потоковой передачи. Это полезно, когда размер содержания известен
     *    и контент не доступен для поиска. По умолчанию размер содержимого определяется с помощью `ftell()`.
     *
     * @return $this Сам объект ответа
     * @throws \Exceptions\Http\Client\RequestedRangeNotSatisfiableException Если запрашиваемый диапазон не выполним
     */
    public function sendStreamAsFile($handle, $attachmentName, $options = [])
    {
        $headers = $this->getHeaders();
        if (isset($options['fileSize'])) {
            $fileSize = $options['fileSize'];
        } else {
            fseek($handle, 0, SEEK_END);
            $fileSize = ftell($handle);
        }

        $range = $this->getHttpRange($fileSize);
        if ($range === false) {
            $headers->set('Content-Range', "bytes */$fileSize");
            throw new \Exceptions\Http\Client\RequestedRangeNotSatisfiableException('Requested range not satisfiable');
        }

        list($begin, $end) = $range;
        if ($begin != 0 || $end != $fileSize - 1) {
            $this->setStatusCode(206);
            $headers->set('Content-Range', "bytes $begin-$end/$fileSize");
        } else {
            $this->setStatusCode(200);
        }

        $mimeType = isset($options['mimeType']) ? $options['mimeType'] : 'application/octet-stream';
        $this->setDownloadHeaders($attachmentName, $mimeType, !empty($options['inline']), $end - $begin + 1);

        $this->format = self::FORMAT_RAW;
        $this->stream = [$handle, $begin, $end];

        return $this;
    }

    /**
     * Устанавливает набор параметров по умолчанию HTTP заголовков для загрузки файлов.
     *
     * @param string $attachmentName Имя файла
     * @param string $mimeType Тип MIME для ответа. Если NULL, `заголовка Content-type` НЕ будет установлен.
     * @param boolean $inline Должен ли браузер открыть файл в окне браузера. По умолчанию ложь,
     * означает, что диалог загрузки появится.
     * @param integer $contentLength Байтовая длина  файла в процессе загрузки. Если NULL, `заголовка Content-Length` НЕ будет установлен.
     * @return $this Сам объект ответа
     */
    public function setDownloadHeaders($attachmentName, $mimeType = null, $inline = false, $contentLength = null)
    {
        $headers = $this->getHeaders();

        $disposition = $inline ? 'inline' : 'attachment';
        $headers->setDefault('Pragma', 'public')
            ->setDefault('Accept-Ranges', 'bytes')
            ->setDefault('Expires', '0')
            ->setDefault('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->setDefault('Content-Disposition', "$disposition; filename=\"$attachmentName\"");

        if ($mimeType !== null) {
            $headers->setDefault('Content-Type', $mimeType);
        }

        if ($contentLength !== null) {
            $headers->setDefault('Content-Length', $contentLength);
        }

        return $this;
    }

    /**
     * Определяет диапазон HTTP заданный в запросе.
     *
     * @param integer $fileSize Размер файла, который будет использоваться для проверки требуемого интервала HTTP.
     * @return array|boolean Диапазон (начало, конец), или ложь, если диапазон запроса является некорректным.
     */
    protected function getHttpRange($fileSize)
    {
        if (!isset($_SERVER['HTTP_RANGE']) || $_SERVER['HTTP_RANGE'] === '-') {
            return [0, $fileSize - 1];
        }
        if (!preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
            return false;
        }
        if ($matches[1] === '') {
            $start = $fileSize - $matches[2];
            $end = $fileSize - 1;
        } elseif ($matches[2] !== '') {
            $start = $matches[1];
            $end = $matches[2];
            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }
        } else {
            $start = $matches[1];
            $end = $fileSize - 1;
        }
        if ($start < 0 || $start > $end) {
            return false;
        } else {
            return [$start, $end];
        }
    }

    /**
     * @todo написать функцию отправки файла с помощью X-Sendfile
     */
    public function xSendFile()
    {
        return $this;
    }

    /**
     * Перенаправляет браузер на указанный URL.
     *
     * Этот метод добавляет "Location" заголовок текущему ответу. Обратите внимание, что он не посылает
     * заголовок до вызова [[send()]]. В действии контроллера вы можете использовать этот метод следующим образом:
     *
     * ```php
     * return Application::$response->redirect($url);
     * ```
     *
     * В других местах, если вы хотите отправить "Location" заголовок сразу, вы должны использовать
     * cледующий код:
     *
     * ```php
     * Application::$response->redirect($url)->send();
     * return;
     * ```
     * В режиме AJAX, это не будет работать, как и следовало ожидать, как правило, если нет какого-то
     * JavaScript кода на стороне клиента для обработки перенаправления кода. Для достижения этой цели,
     * Этот метод будет посылать заголовок "X-Redirect" вместо "Location".
     *
     * В JavaScript коде вам будет необходимо обработать этот заголовок и произвести перенаправление на стороне клиента.
     *
     * @param string $url URL-адрес для перенаправления
     *
     * Любой относительный URL будет преобразован в абсолютный, используя информацию из текущего запроса.
     *
     * @param integer $statusCode код состояния HTTP. По умолчанию 302.
     * Смотри <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
     * Подробную информацию о кодах статуса HTTP
     * @param boolean $checkAjax следует ли специально обрабатывать AJAX (и PJAX) запросы. По умолчанию true,
     * означает, если текущий запрос является запрос AJAX или PJAX, то вызов этого метода заставит браузер
     * перенаправить пользователя на данный URL. Если это ложь, то будет послан заголовок `Location`, который
     * может не активировать перенаправление браузера при AJAX/PJAX ответе.
     * Действует только при отсутствии заголовка `X-Ie-Redirect-Compatibility`.
     * @return $this сам объект ответа
     * @todo Сделать класс-помощник [[Url]]
     */
    public function redirect($url, $statusCode = 302, $checkAjax = true)
    {
        if (is_array($url) && isset($url[0])) {
            // ensure the route is absolute
            $url[0] = '/' . ltrim($url[0], '/');
        }
        //$url = Url::to($url);
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = Application::$request->getHostInfo() . $url;
        }

        if ($checkAjax) {
            if (Application::$request->getIsAjax()) {
                if (Application::$request->getHeaders()->get('X-Ie-Redirect-Compatibility') !== null && $statusCode === 302) {
                    // Ajax 302 redirect in IE does not work. Change status code to 200. See https://github.com/yiisoft/yii2/issues/9670
                    $statusCode = 200;
                }
                if (Application::$request->getIsPjax()) {
                    $this->getHeaders()->set('X-Pjax-Url', $url);
                } else {
                    $this->getHeaders()->set('X-Redirect', $url);
                }
            } else {
                $this->getHeaders()->set('Location', $url);
            }
        } else {
            $this->getHeaders()->set('Location', $url);
        }

        $this->setStatusCode($statusCode);

        return $this;
    }

    /**
     * Обновляет текущую страницу.
     * Эффект этого метода такой же, как если бы пользователь нажал кнопку обновления в своём браузере
     * (Без повторной отправки данных).
     *
     * В действии контроллера вы можете использовать этот метод следующим образом:
     *
     * ```php
     * return Application::$request->refresh();
     * ```
     *
     * @param string $anchor якорь, который должен быть добавлен к перенаправляемуму URL.
     * Значения по умолчанию для опорожнения. Убедитесь, что якорь начинается с '#', если вы хотите указать его.
     * @return Response сам объект ответа
     */
    public function refresh($anchor = '')
    {
        return $this->redirect(Application::$request->getUrl() . $anchor);
    }

    private $_cookies;

    /**
     * Возвращает коллекцию cookie
     * Через возвращённую коллекцию cookie, можно добавить или удалить куки следующим образом,
     *
     * ```php
     * // Добавление cookie
     * $response->cookies->add(new Cookie([
     *     'name' => $name,
     *     'value' => $value,
     * ]);
     *
     * // Удаление cookie
     * $response->cookies->remove('name');
     * // Альтернатива
     * unset($response->cookies['name']);
     * ```
     *
     * @return CookieCollection Коллекция cookie
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection;
        }
        return $this->_cookies;
    }

    /**
     * @return boolean Имеет ли ответ допустимый [[statusCode]].
     */
    public function getIsInvalid()
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    /**
     * @return boolean Является ли ответ информативным
     */
    public function getIsInformational()
    {
        return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
    }

    /**
     * @return boolean Является ли ответ успешным
     */
    public function getIsSuccessful()
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * @return boolean Является ли ответ перенаправлением
     */
    public function getIsRedirection()
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * @return boolean Является ли ответ ошибкой клиента
     */
    public function getIsClientError()
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * @return boolean Является ли ответ ошибкой сервера
     */
    public function getIsServerError()
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * @return boolean Является ли ответ нормальным
     */
    public function getIsOk()
    {
        return $this->getStatusCode() == 200;
    }

    /**
     * @return boolean Является ли ответ запрещенным
     */
    public function getIsForbidden()
    {
        return $this->getStatusCode() == 403;
    }

    /**
     * @return boolean Является ли ответ не найденным
     */
    public function getIsNotFound()
    {
        return $this->getStatusCode() == 404;
    }

    /**
     * @return boolean Является ли этот ответ пустым
     */
    public function getIsEmpty()
    {
        return in_array($this->getStatusCode(), [201, 204, 304]);
    }

    /**
     * @return array Форматировщики по умолчанию
     */
    protected function defaultFormatters()
    {
        return [
            self::FORMAT_HTML => 'yii\web\HtmlResponseFormatter',
            self::FORMAT_XML => 'yii\web\XmlResponseFormatter',
            self::FORMAT_JSON => 'yii\web\JsonResponseFormatter',
            self::FORMAT_JSONP => [
                'class' => 'yii\web\JsonResponseFormatter',
                'useJsonp' => true,
            ],
        ];
    }

    /**
     * Подготавливает отправляемый запрос
     * Реализация по умолчанию будет преобразовывать [[data]] в [[content]] и установить заголовки соответствующим образом.
     *
     * @throws \Exception\InvalidConfig если форматировщик для указанного формата является недействительным или [[format]] не поддерживается
     * @todo Оптимизировать метод под текущий движок
     */
    protected function prepare()
    {
        if ($this->stream !== null) {
            return;
        }
        /*
        if (isset($this->formatters[$this->format])) {
            $formatter = $this->formatters[$this->format];
            if (!is_object($formatter)) {
                $this->formatters[$this->format] = $formatter = Yii::createObject($formatter);
            }
            if ($formatter instanceof ResponseFormatterInterface) {
                $formatter->format($this);
            } else {
                throw new \Exception\InvalidConfig("The '{$this->format}' response formatter is invalid. It must implement the ResponseFormatterInterface.");
            }
        } elseif ($this->format === self::FORMAT_RAW) {
            if ($this->data !== null) {
                $this->content = $this->data;
            }
        } else {
            throw new \Exception\InvalidConfig("Unsupported response format: {$this->format}");
        }

        if (is_array($this->content)) {
            throw new InvalidParamException('Response content must not be an array.');
        } elseif (is_object($this->content)) {
            if (method_exists($this->content, '__toString')) {
                $this->content = $this->content->__toString();
            } else {
                throw new \Exception\InvalidParam('Response content must be a string or an object implementing __toString().');
            }
        }*/
    }
}
