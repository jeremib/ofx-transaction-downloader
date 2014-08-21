<?
	class OFX {
		public $bank;
		public $request;
		public $response;
		public $responseHeader;
		public $responseBody;
		public function __construct($bank, $request) {
			$this->bank = $bank;
			$this->request = $request;
		}
		public function go() {

			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $this->bank->url);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/x-ofx'));
			curl_setopt($c, CURLOPT_POSTFIELDS, str_replace("\n", "\r\n", $this->request));
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
			$this->response = curl_exec($c);

			curl_close ($c);
			$tmp = explode('<OFX>', $this->response);
			$this->responseHeader = $tmp[0];
			$this->responseBody = '<OFX>'.$tmp[1];
		}
		function xml() {
			$xml = $this->responseBody;
			self::closeTags($xml);
			$x = new SimpleXMLElement($xml);
			return $x;
		}
		static function closeTags(&$x) {
			$x = preg_replace('/(<([^<\/]+)>)(?!.*?<\/\2>)([^<]+)/', '\1\3</\2>', $x);
		}
	}
	
	class Finance {
		public $banks;
	}
	
	class Bank {
		public $logins; // array of class User
		public $finance; // the Finance object that hold this Bank object
		public $fid;
		public $org;
		public $url;
		function __construct($finance, $fid, $url, $org) {
			$this->finance = $finance;
			$this->fid = $fid;
			$this->url = $url;
			$this->org = $org;
		}
	}
	
	class Login {
		public $accounts;
		public $bank;
		public $id;
		public $pass;
		function __construct($bank, $id, $pass) {
			$this->bank = $bank;
			$this->id = $id;
			$this->pass = $pass;
		}
		function setup() {
			$ofxRequest = 
			"OFXHEADER:100\n".
			"DATA:OFXSGML\n".
			"VERSION:102\n".
			"SECURITY:NONE\n".
			"ENCODING:USASCII\n".
			"CHARSET:1252\n".
			"COMPRESSION:NONE\n".
			"OLDFILEUID:NONE\n".
			"NEWFILEUID:NONE\n".
			"\n".
			"<OFX>\n".
				"<SIGNONMSGSRQV1>\n".
					"<SONRQ>\n".
						"<DTCLIENT>20110412162900.000[-7:MST]\n".
						"<USERID>".$this->id."\n".
						"<USERPASS>".$this->pass."\n".
						"<GENUSERKEY>N\n".
						"<LANGUAGE>ENG\n".
						"<FI>\n".
							"<ORG>".$this->bank->org."\n".
							"<FID>".$this->bank->fid."\n".
						"</FI>\n".
						"<APPID>QMOFX\n".
						"<APPVER>1900\n".
					"</SONRQ>\n".
				"</SIGNONMSGSRQV1>\n".
				"<SIGNUPMSGSRQV1>\n".
					"<ACCTINFOTRNRQ>\n".
						"<TRNUID>".md5(time().$this->bank->url.$this->id)."\n".
						"<ACCTINFORQ>\n".
							"<DTACCTUP>19900101\n".
						"</ACCTINFORQ>\n".
					"</ACCTINFOTRNRQ> \n".
				"</SIGNUPMSGSRQV1>\n".
			"</OFX>\n";
			$o = new OFX($this->bank, $ofxRequest);
			$o->go();
			$x = $o->xml();
			foreach($x->xpath('/OFX/SIGNUPMSGSRSV1/ACCTINFOTRNRS/ACCTINFORS/ACCTINFO/BANKACCTINFO/BANKACCTFROM') as $a) {
				$this->accounts[] = new Account($this, (string)$a->ACCTID, 'BANK', (string)$a->ACCTTYPE, (string)$a->BANKID);
			}
			foreach($x->xpath('/OFX/SIGNUPMSGSRSV1/ACCTINFOTRNRS/ACCTINFORS/ACCTINFO/CCACCTINFO/CCACCTFROM') as $a) {
				$this->accounts[] = new Account($this, (string)$a->ACCTID, 'CC');
			}
		}
	}

	class Transaction {
		public $type;
		public $datePosted;
		public $amount;
		public $identifier;
		public $name;

		public function __construct($type, DateTime $datePosted, $amount, $identifier, $name) {
			$this->type = $type;
			$this->datePosted = $datePosted;
			$this->amount = $amount;
			$this->identifier = $identifier;
			$this->name = $name;
		}
	}
	
	class Account {
		public $login;
		public $id;
		public $type;
		public $subType;
		public $bankId;
		public $ledgerBalance;
		public $availableBalance;
		public $transactions = array();
		public function __construct($login, $id, $type, $subType=null, $bankId=null) {
			$this->login = $login;
			$this->id = $id;
			$this->type = $type;
			$this->subType = $subType;
			$this->bankId = $bankId;
		}
		public function setup() {
			$ofxRequest = 
				"OFXHEADER:100\n".
				"DATA:OFXSGML\n".
				"VERSION:102\n".
				"SECURITY:NONE\n".
				"ENCODING:USASCII\n".
				"CHARSET:1252\n".
				"COMPRESSION:NONE\n".
				"OLDFILEUID:NONE\n".
				"NEWFILEUID:NONE\n".
				"\n".
				"<OFX>\n".
					"<SIGNONMSGSRQV1>\n".
						"<SONRQ>\n".
							"<DTCLIENT>20110412162900.000[-7:MST]\n".
							"<USERID>".$this->login->id."\n".
							"<USERPASS>".$this->login->pass."\n".
							"<LANGUAGE>ENG\n".
							"<FI>\n".
								"<ORG>".$this->login->bank->org."\n".
								"<FID>".$this->login->bank->fid."\n".
							"</FI>\n".
							"<APPID>QMOFX\n".
							"<APPVER>1900\n".
						"</SONRQ>\n".
					"</SIGNONMSGSRQV1>\n";
			if($this->type == 'BANK') {
				$ofxRequest .=
					"	<BANKMSGSRQV1>\n".
					"		<STMTTRNRQ>\n".
					"			<TRNUID>".md5(time().$this->login->bank->url.$this->id)."\n".
					"			<STMTRQ>\n".
					"				<BANKACCTFROM>\n".
					"					<BANKID>".$this->bankId."\n".
					"					<ACCTID>".$this->id."\n".
					"					<ACCTTYPE>".$this->subType."\n".
					"				</BANKACCTFROM>\n".
					"				<INCTRAN>\n".
					"					<DTSTART>20140101\n".
					"					<INCLUDE>Y\n".
					"				</INCTRAN>\n".
					"			</STMTRQ>\n".
					"		</STMTTRNRQ>\n".
					"	</BANKMSGSRQV1>\n";
			} elseif ($this->type == 'CC') {
				$ofxRequest .=
					"	<CREDITCARDMSGSRQV1>\n".
					"		<CCSTMTTRNRQ>\n".
					"			<TRNUID>".md5(time().$this->login->bank->url.$this->id)."\n".
					"			<CCSTMTRQ>\n".
					"				<CCACCTFROM>\n".
					"					<ACCTID>".$this->id."\n".
					"				</CCACCTFROM>\n".
					"				<INCTRAN>\n".
					"					<DTSTART>20140101\n".
					"					<INCLUDE>Y\n".
					"				</INCTRAN>\n".
					"			</CCSTMTRQ>\n".
					"		</CCSTMTTRNRQ>\n".
					"	</CREDITCARDMSGSRQV1>\n";
			}
			$ofxRequest .=
				"</OFX>";
			$o = new OFX($this->login->bank, $ofxRequest);
			$o->go();
			$x = $o->xml();
			$a = $x->xpath('/OFX/*/*/*/LEDGERBAL/BALAMT');
			$this->ledgerBalance = (double)$a[0];
			$a = $x->xpath('/OFX/*/*/*/AVAILBAL/BALAMT');
			if(isset($a[0])) {
				$this->availableBalance = (double)$a[0];
			}
			$t = $x->xpath('/OFX/*/*/*/BANKTRANLIST/STMTTRN');
			if ( isset($t[0]) ) {
				foreach($t as $trans) {
					$this->transactions[] = new Transaction((string)$trans->TRNTYPE, new DateTime((string)$trans->DTPOSTED), (double)$trans->TRNAMT, (string)$trans->FITID, (string)$trans->NAME);
				}	
			}
			
		}
	}
