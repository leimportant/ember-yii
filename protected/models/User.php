<?php
/**
 * User.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 7/22/12
 * Time: 11:42 PM
 */
/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $id
 * @property string $password
 * @property string $salt
 * @property string $email
 * @property string $username
 * @property string $login_ip
 * @property integer $login_attempts
 * @property integer $login_time
 * @property string $validation_key
 * @property string $reset_token
 * @property string $password_strategy
 * @property boolean $requires_new_password
 * @property integer $create_id
 * @property integer $create_time
 * @property integer $update_id
 * @property integer $update_time
 * @property integer $delete_id
 * @property integer $delete_time
 * @property integer $status
 */
class User extends CActiveRecord
{

	/**
	 * @var string attribute used for new passwords on user's edition
	 */
	public $new_password;

	/**
	 * @var string attribute used to confirmation fields
	 */
	public $password_confirm;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Customer the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * Behaviors
	 * @return array
	 */
	public function behaviors()
	{
		Yii::import('application.behaviors.password.*');
		return array(
			// Password behavior strategy
			"APasswordBehavior" => array(
				"class" => "APasswordBehavior",
				"defaultStrategyName" => "bcrypt",
				"strategies" => array(
					"bcrypt" => array(
						//"class" => "ABcryptPasswordStrategy",use this for PHP versions >= 5.3
                        "class" => "AHashPasswordStrategy",//for demo purposes
						"workFactor" => 14,
						"minLength" => 8
					),
					"legacy" => array(
						"class" => "ALegacyMd5PasswordStrategy",
						'minLength' => 8
					)
				),
			)
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email', 'required', 'on' => 'checkout'),
			array('email', 'unique', 'on' => 'checkout', 'message' => Yii::t('validation', 'Email has already been taken.')),
			array('email', 'email'),
			array('username, email', 'unique'),
			array('password_confirm', 'compare', 'compareAttribute' => 'new_password', 'message' => Yii::t('validation', "Passwords don't match")),
			array('new_password', 'length', 'max' => 50, 'min' => 1),
			array('password_strategy', 'safe'),
			array('email, password, salt, reset_token', 'length', 'max' => 255),
			array('requires_new_password, login_attempts', 'numerical', 'integerOnly' => true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, password, salt, password_strategy , requires_new_password , email', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => Yii::t('labels', 'Username'),
			'password' => Yii::t('labels', 'Password'),
			'new_password' => Yii::t('labels', 'Password'),
			'password_confirm' => Yii::t('labels', 'Confirm password'),
			'email' => Yii::t('labels', 'Email'),
		);
	}

	/**
	 * Helper property function
	 * @return string the full name of the customer
	 */
	public function getFullName()
	{

		return $this->username;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('username', $this->username, true);
		$criteria->compare('password', $this->password, true);
		$criteria->compare('email', $this->email, true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria' => $criteria,
		));
	}

	/**
	 * Makes sure usernames are lowercase
	 * (emails by standard can have uppercase letters)
	 * @return parent::beforeValidate
	 */
	public function beforeValidate()
	{
		if (!empty($this->username))
			$this->username = strtolower($this->username);
		return parent::beforeValidate();
	}

	/**
	 * Generates a new validation key (additional security for cookie)
	 */
	public function regenerateValidationKey()
	{
		$this->saveAttributes(array(
			'validation_key' => md5(mt_rand() . mt_rand() . mt_rand()),
		));
	}

}