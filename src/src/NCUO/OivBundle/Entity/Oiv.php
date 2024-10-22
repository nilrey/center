<?php

namespace App\NCUO\OivBundle\Entity;

use App\NCUO\OivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;


/**
 * Oiv
 *
 * @ORM\Table(name="oivs", schema="oivs_passports")
 * @ORM\Entity(repositoryClass="OivRepository")
 */
class Oiv
{

    /**
     * @var string
     *
     * @ORM\Column(name="id_oiv", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="oivs_passports.seq_oiv_id_oiv", allocationSize=1, initialValue=1)
     */
    private $id_oiv;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="descr", type="string", nullable=true)
     */
    private $descr;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_oiv_type", type="integer", nullable=false)
     */
    private $id_oiv_type;

     /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */  
    protected $enabled;
    
    /**
     * @var string
     *
     */
    public $heraldic_img;
    
    /**
     * @var string
     *
     */
    public $name_short;
    


    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id_oiv;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Oiv
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set id_oiv_type
     *
     * @param string $id_oiv_type
     * @return Oiv
     */
    public function setIdOivType($id_oiv_type)
    {
        $this->id_oiv_type = $id_oiv_type;

        return $this;
    }

    /**
     * Get id_oiv_type
     *
     * @return integer 
     */
    public function getIdOivType()
    {
        return $this->id_oiv_type;
    }

    /**
     * Get descriptionText
     *
     * @return string 
     */
    public function getDescriptionText()
    {
        return $this->descriptionText;
    }

    /**
     * Set descr
     *
     * @param string $descr
     * @return Oiv
     */
    public function setDescription($descriptionText)
    {
        $this->descr = $descriptionText;

        return $this;
    }
    
    /**
     * Set heraldic_img
     *
     * @param string $heraldic_img
     * @return Oiv
     */
    public function setHeraldicImg($heraldic_img)
    {
        $this->heraldic_img = $heraldic_img;

        return $this;
    }

    /**
     * Get heraldic_img
     *
     * @return string 
     */
    public function getHeraldicImg()
    {
        return $this->heraldic_img;
    }
    
    /**
     * Set name_short
     *
     * @param string $name_short
     * @return Oiv
     */
    public function setNameShort($name_short)
    {
        $this->name_short = $name_short;

        return $this;
    }

    /**
     * Get name_short
     *
     * @return string 
     */
    public function getNameShort()
    {
        return $this->name_short;
    }

    /**
     * Set setNoHeraldicImgBase64
     *
     * @return Oiv 
     */
    public function setNoHeraldicImg(){
        $this->setHeraldicImg('<img src=" data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJkAAACZCAYAAAA8XJi6AAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wICQY2DGB301MAACAASURBVHja7X1diGVXte43195r/1Ttqi7NRVSECKK5LQg+COa+eBAEL0h8CYpPBhI4HrFF8WDgHDwQUBQ8eK5i5KiQQPIkSl4MwhUE0ZcbwQdBsG8MgoGgIjdaXbX/f9a8D72/db499phrr6qu7to7qQVNd++ftdeaa8zx840xvhGm0ym2+YgxIoSQfA/Amd8PIaAoCv77EMC7ALwDwNsBvA3AWwC8CcAbAVwD0APQBZADCAAigBmAEYA+gFshhL/FGP8K4M8AXokx/hHAHwD8HsBJ6hp5PbzWOve9a0fYdiHbJIDew9KHpq8BeDOA9wN4X4zxvSGE9wC4P/WwvYef+u2U8CyPlwH8FsBvAPwawK8A/KWOsF0J2WVf/FIjZVlW/h1jRIwRjUYDMcb7AHwoxvhBAB8AcJ3vF0WBoigQY8R8Pi//TQGymkTfS11LCAFZlpX/DiGg0Wig0Wig2WyuCFGM8WYI4ZcAfh5j/FmM8dUsy16TwrbTQqaCRoEIITwQY/xojPEjIYR/oPDEGDGbzUqBUuHh9/mnSshS5tkTCtWiKnTNZhN5npebYnm+XwD4CYAfA3jxSpNtmb+WZdlbi6L4BICPAXiQWopCNZ/PVx62aivrt6kAbfL1rIbTc+tvqfBazZfnOfI8R7PZ1PO9EEL4UQjhBzHGP10J2SVqsBjjQwAeAfBwCAHz+RzT6RTT6RSLxQKNRgMA1kypFaC77WTrb3qaktfXbDbRarXQarVKFyCE8FyM8RkAz18J2b072gA+DeAfi6K4Tp9qMplgNpuVJgkAFotF+SVjmsqHm9JQ9K8uQrDs//m7vCY1rUVRoNFooN1ul6Z1eR03AXwfwH8CmFwJ2d057gPweQA3ABwtFgtMp1OMx+NSE6R8ohjjyvteBHkW/0vNrQpxnSgx5fOljkajgVarpT7ccQjhSQDfBPDqlZDdIQ62PA5jjI+HEP4ZQGc2m2EymWAymaw8TGqvs+BreqhW8cyrJ3z2/NbJV03pCfEmIbPmvdPpoNVqodFoIIQwjjF+A8DXY4wn6v9RI96JJt45IUvt7JQgyE7/YgjhX2OMR7PZDOPxGLPZrBSoOhqoSqsw8qRprBtBesJVJSTe+auETAXFBiQhhBW/LYRwHGP8agjh3z188EqTGWGTBfp4COGJGON1aq35fO4+FG8xFZJIRXsxxtJns+/Za7IR56aHJ9mEc2kwPY9igNSONP/tdrv03WKMNwE8EUL4of7+lZAZHwXAO0MIXwshPEzNxevTyCz14Lj7reNe18fS31EtUqV5Peiiyjxv+uwm06n3xP/v7e2h3W7ztecA/EsI4aXXnU/mLaoKWIzxC1mWfT2E0BgOhxiPx2vpoRACFotFKQDNZnNFqKwPldJ8VQLm4V4edkaB5oO28ISn9Wyk6ZlQ79rsPar5zbKshGtE2BZFUTweQviP16Umsw9vsVg80Gg0vhVC+PB4PMZwOFzBuDxnO4XI2/dtVGl/32qrRqOBLMvKv1UjUpDUjM7n85XzLhYLN0XlBQt2HVI+q3ed9nsaZLTbbezt7aHZbGKxWPwUwOdwyRmEuy5kKWd0uTiPhRCeLIqiMxqNSu3laaWqqK6OSVIhZD4xxohms1liUVaAN4GoqpGsCaPQ8c98Pl/B7ayQ1AmMbG612WyuRb9FUaDZbGJvbw95ngPAeAn7PPWa1mT2YSx353ezLPvUdDrFcDjEfD6vhCE8cNMi5p4Wo1a0OcMqXOu8GYAqX47XSYGjFqQG0t+3jrsCyWeJGlutFvb29vid7wH4p8uIQO+qkCUe3nUAT8cYH5zNZuj3++Xu4wOSlMpapOgJkv0dCh1zgnmel5rrsoMcvYfZbIbpdLoidKmIVIWP91wnOm02m+h0Omi32yiK4gUAj4YQblZtpJQ7sRVCltoZjUaDi/gQgGcBHA0GA8xms1JQKBgKK3jYlQKNXIDFYlGep9lsot1uI8/zpGndlkPvbT6fl0LnaXOui2q1TYGDbsROp4NOp4OiKI5DCJ/Msuz5ixKiSxUy43N8Jsb4ZIwRp6enWCwWbpXCYrFYEaAURGAXlwDlEg3fKpyoTsaBQrNYLGCxQevz1REwi/XFGNHpdLC3t8e3bwD4zh1kYrbHJ1suxJdDCF+az+fo9/tr/hWTwnZRNb2TSs0QkFRz6/lw1nHfKkTcuTb6bhS2KkjmLPeR53kpaCGErwD4t0343y44/t8GcEOjRx6qzXhj9J1s5KWmMYSAbrdb+lq2wmIT4n1W5P1e+2xesKBVJrox6+JsDHb4vWvXrnFTPhlj/OzdXI+77fg/FUJ4dDAYYDqdrjx8m5dL7ZzFYoFms1kucLvdLh1Z+mK2ArUKFtiF0maveiTGCEbiiiPWjepVU1LQDg8PKahPxxgf28Xo8pmiKD7JpDZ9LU1Kq1BwIaqqB/b29ui8JnOLGhWl8LTL0mAeTJIy3SoQCmWoVrP1cFW/m2VZ6Y6oT9fr9dh/8GyM8ZGtFTJHmzxVFMWjdGC5UCkgU+EKXRS+1m630e12V0xpyoHe5O9ctnaq+15VYp5arapwQNdx0wajoMUYnw4hPLbVmmy5CN8OIdwYDocrGqxKKG3IzSBA83FefdZrsX2szmZWDTcYDMraOi+q9za0Wg7+++DggOd+MoTw2YvclNlZdmKNUPzLIYQbk8mksmLVnoNJb8IP8/kcnU4Hh4eHaLVaa878a6Xp9byBgQrA/v4+er3eyqbjenraUcFc1XT9fp/nvBFj/PJFFjxe2JmyLPsMgC+Nx2OMRqNa0ZvuTPobMcaVhbOfU7T/9SpsVmi4IfM8Lze2Bgae3+fV4J2cnNCSfKkois9sjblcXuxDIYQfT6dTDAaDNV8rdWMKWfA7+/v7rCAo37sSKF+jaZqJ//cKDVLWSLMJNJ95nuPw8JDr/9EY4/OXqsmWGug6gGfn8zmGw+Hahdubs+Uu6mcxLWR9NI3Crg6smEL6Z/P5vNyknU6ndENS1Sx6Lh6NRgPMJy+F7lkA171nd8+EbHkDTwM4ok23KRDbRFvlZ8xmsxIH8sqhtxVEvSxNpn9rAef+/v5KNJ6yBAqNUDE0Gg1MJhNMp1PEGI9CCE/bTX+vfbLvZln2IE2khSq86lKbf6Ojys8Ph8MySezd1Hlv9LXskylcwe75TqdTuh7ed9TUapUtfTqiA0VRPAjgu/fEXDoP9rEQwqcoFFpFYUtwlOTEcz654yh0w+EQk8lkTWtdCZePr3kBEikQ9vb23O5573tW400mE37vUzHGx6z7ciGOv0IQptT3gRDCb+bzeef09HQNsfeKDxWx3oTGUyO2223s7++vCViqOPDq8I/JZFJG/N6zrToajQYj/XEI4b1FUbxYpwKktibzgNKlIHwrxtgZDAZrSW6vgJB/a6JWS5G1hkwj0/F4jMFgsJZaqUKvr471gxkTXfO6RY9spgbQiTF+6zwgeG0hE9X6hRjjh8fjMebzecm7Zf0u3Ql0KG3FpTWX+l3W3XMX8jtqjq/MZ31Xh4JWN9+pft5kMqGAfhjAF84afG3EyYzGeCeAm7PZrCFh7oqZI/ZFDUXh4meazSbG4/FaF7iW8mjpDs/f7XbLevWzLNTrXbgsXjkcDjEajUrgturQZ9FsNpl6WoQQrscYX7oQTeZI7NfYF2kdes2JWdPJmqi9vT30ej3s7e2Vr6kvZ3OTmg0YjUbo9/trHT9Xx2ZBU0uhZVKbtJG6KGy2zrKsEWP82oVHl8sL/HgI4eHRaFQCfzZCodayDQ9FUWB/fx95npflJd1ud6V2XcDdtYCB56bp3BYikV3B0yy0tL+/X6vAQP1frv9sNgOAhwF8/MJ8MjFbTxRFgeFwuFLqrJiXBfeoctnEwBteLBZlE6pGOqrJrPDxM9PpFP1+/wrSOMPhOfzdbnfNaqQYJPW5CCb6xIUJ2fKivphl2fXhcFg68upPKWJswb5Go1EKmGoqstO02+3yNdVQFtvRBWAS/krI6h22Dk8rjJVT1woZ3R91acgLt0wnfrGWRrW1SA40cBhCeHkymRxRg1i2aVs2olWwBwcHKxGoB8T2+33a+xUMLEVFQAEmqk0BT1GspzS0re44D6fXrkMp/X6/5NS1AQPvjbV9XCdiZ41G4xjA/THGk6pK5cwjQTHH4wCOiP56MIX+gIKx7XYbrVbLJVpRgep2u+h0OmVLf6roTn+r0WhgOp2CPqKXz6tLLGyrGbwdncrDbruAVeWM2Y/pBVqWRVIVC4tRY4xHMcbHU79VPosNEMZ9AF6ZTqcdJsA9YbLSS+ji2rVrlaQilod/NBqtpJOs8OoCqFNLeENr++80I1DHMa7iyrjIyt27WQUcQijTeFpBo1kbLbsikJ7neZkJiDG+LYTwaup6sw2L+HkAnclksuLMe4tqF4GYlvdANABQQet2u2i3227gYU2gVsqORiMMh8MV37BKwC7y4VtyGE8b3qkmupu+Z1EU2NvbW0MGvChT04Kz2YwdZB3c5vJNDtRY0WRGI7UB/GU6nR4p8GrbtTwt1mq1yr4+T9PZpgjL16rkd1XAIs/FQET7Ac6qJbz6eQWYLbneJirOizZ5qQjwTrU1LdJkMikbr73mEy+5nmUZDg8PEUI4BvDmGKPLyt2sSCN9OsZ4ZAmArf+i6pUXyPSFDQg8SbdRJGmPGEVa7eBpNP42QeL9/f0ztYtZYSPNEx1ial2NtnhNHPZwNwldrG96nkqIqowAFUOe56W7ktL8+pvk78jz/Ai3ae+/mUwrJXbE7+bz+fWTk5O1SlbWkHtOeqfTQa/XW2vV8vwXVdmse+I5F4tFWcSYoi1wNkZZQqzmuqpoj4LF0JwtfJb0bpPJJYuQ5eO4CC3GtaBQn+f8dagIiqLArVu3XO2ZEvQ8zxnh3wTwbm9jZInd8hCA68oZRvVIthxNV6hWy/PczQjYoGHlIqTYTrUfS4m9Zt1U5AncLm0Zj8eVlOja2TMcDsEud5upUII8jb5Ij6DXQZPT7/dLobjTg5Uoo9EIo9EIp6enZcHAebRh1WuMNlMWw2tIFrfm+lJu1nzJZsLuP8KdbTErFRBLNUDKJiswVSyFKUpOquNer1cudmoghLczR6MRFosFDg4OVsw8hYRNL5qsV+2mNOYpn0gHVmjkO5/PcXp6WmY6tDQ6pUW8TTOZTEAA3Aqewg8evmV9zBQNlwoROUaYutMiU6/qha9NJhMK5yMhhOddxN9I7lsBPEwsxFsYj4GG5STeoqVGwHj+mf33YrEoa9b1eqoElj4TH5Il0RuNRjg5OSn5NewDISRCbaXaTH2yPM/R7XbR7XZXTDpB5ul0itPT0zVYJsVlq4LIWi5SdqofGEJgDf6apvZGKmrAtMndYIYmZT08jUjfeZnTfKsLYZgy6U/wJlImynu4ZDX0OPOrtI/etAoO6ZK4QET3LRmc7kRLjcladevX0f/jour9N5tNdLvd2nQIpDhPEb1Qq9HEaZrGA3hT5erWXHlJcJs39nxShY3sdwmGp+rO7OwDXhsHpy3X+BP2OjOrAQB8TCMr6+x5Zo4jWewcyTqouF14RnZWY2ZZhlarVaaRbOGjFylqmRATu9QybMu3fkadUYT2+lXgrAan30pYhu1rVsBsDtGDiGxjMzcWv8vnxro9u8bWf7YujPrD9M0Suew17SfpyY9ZJCJQYy1/7AEA/5dOpld+7dEz8aGlds+mKIcPwHLj82J7vV4ZcACrjSbWjFfxrZIVSCnSB4PBykPL8xxHR0e16tbUb2H3ted7Wh5cK4Apoj4loqmqBE5tSMIS+sCrnov+hvL52hrB1HmuXbvGz/z3GOOLK5pMJO+jRVGsDcdK2W9+1wNAN5kYqtlbt25hMBisUJBb55XvUwg16vTmD3k+mqZP+LqE3yuINjMcdbQwH4iaaC1b8oZ5qYCqP6ZVLJYrNpW6svlcfmYymaysra2AqRIwnTCcEihvXRhRF0Xx0RX/2gjDRzRJ7U3YsLgKZ/yk0keeP7dYLMpUkJZr25Ih1U79fr98+Kyy9aIrvTbbzd5oNEofjYfSW/L7bPPzfCPVVgQjGcmq867Qj3ZyW/Pqra310ywZcRWQrPfeaDTAARyaxrMBlNWSSvK8aVy23g/9shDCR1YUEs1lURT3hRD+n5rKVKOu3lyz2USv16s16IrvK2dGVdrJUq4TOyNM0mg0SgxJP6cbwCbibc+Amge9pqIoSggjhb6z631TeuesqaYUX9lZm2c0sszzHAcHBxu5RfR5LxaLku3Hzmrn+ts88Rve8Aa+/9+YNM9kwT6k0YMXYlsB48WnQnKrwbj7ubNSqtcmm6mJFosFTk9PMZvNVjQaKzBSQYBXuKdJdWo0bhb+NjEwkphQK3D2Ux0BO0+y/KJoSNVMsyxq0zks9YGdeuL5j5odkTX50FoVRqPR+KBSnFcNaVDkm/Vim2Y+KsJexQ7o7Sol1C2KohQ0ZWLc29tb2VVepKfRE6PO09PT8nPNZhOHh4driXALedjW/lRV6TYcGjXbLEid72qUak26Z/Lpl4UQPrgmZDHGD1AA1K5XYWQcJ3MW3nxF7ut0y1gyY2okpm54k+So9+Z7U7htBQcBW6V9Z6TsRXla8lJFfryNB+9DN2KdejnVZCnYQw8qkBjjB6yQvRnA9aqw3SPR5QWk6oi8Eh0PTN3kUyihrob+JAWhoGs9mi0Pr8LUxuNxWbRnAVY+FAsaVwU620Zzpc9Ic6p1rlG19iYtbTIO15dyVYKx76cUegCgW4i2RPmrUkYWGLWVrnUOpVjn+Xidi8UCJycnK8MUer3eGscshVXLcWzDMfOYfI3Ar4zsWYMXNt37NhyKFFggts7B9FnVwA4bbcqIxver4/8+vpnahV6OSxO3m4BCHYhquTCqtCe1oKptMjFScG7durWCs/V6vRKxVkFLRbF8ncEAhVEFjee2if9UJLiNQmcB2braVp+zQkOpdRQhe5+ay/dqWqOqgcOmUFJ4WCo9wt1Ud5qGV6KjQQQfPolZFLBlA7EXvWo6hkIcQih9NBv6c1N5QxqqZnNuizbzNO4ma6J+qicTqSIIEbL3aqnPeyy/mN0B6gvVadKwgqGzJ+mw162BsnMfLWLOf/f7ffR6PbRaLczn8xIHs+12HrzC2rkYY0mzRLCXTTHq19BXpAa1zbDbQkHKiFwhIW89q3wsBWW9AlJbHygIxXsoZIcA7vcouVOAKlMOZwUG79SEePVMvGGatNPT01LQSIlAU6jfsf0C9v/8PEu51UTy3Bres6qWApgqObeDYO+2w7+JGPqswmq1H+XB4p7LgOx+AIdZCOFdHmSx6eIvssT4ToXO4mhamUocTVNQ1Kq2ISRVvZEqDlBTQTD34OBgpXAzlciuMxtpW6APC9t4boJXRbs83pXFGN+hdtv7kufgbgPpiV4TF0FxNJYGsxCPdVI022rmFEfThSXVknWaFenWRSc7pAYeVam5XTm4cdRPS8mB+rsA3pEBeHsdfEdf35ZdaKMZi6Oxu5yvsRzaArteklkXczQalUWcKexQGSTZsbW/v59MPu+akGnC33IBa3DgFGK+PQPwNhvJeYlw2/ixLYtEx1TziMTRiqIAKUc1wU7TabWS3TwKHDMRb/lvtearirhP5xrsIomflQ3NxFj8UBuDALwtCyG8xX6pKvy2Du9l37jiaLrrNAdL8jwKApuArWBYv8ymlLTiwzODXnMG86pe99auHJrv3cQbYjcogLdkMcY3pZy4lI9W1UF9GY6/p74V7aZG07wsifg2pZ10A2qXUx14QjWa1s1fNJXBvTi00sLbJBWR65syAG/0vlRlMrct8vFq9LUAkveh1AfsSrIazeJo3n0rP1qq0sNiTdpe56Hm2y5cqRGG9jk48vLGDMC11OLUlNStELSqReLntIKX93BwcFAKGj9va9q9qlUt7lRfJIUHNptN7O/v7xyfWd3n7kEbyzW9lgHo1fET9DPbYi43CbtunjzPSyHTe2GDCc1ZqrXM4+tSalGrQa0JpjZjQ8wuEucxgqwCeC1rE4BeBqBrfTLPr/BU6K7sRNuy5zVjKCOQV9dv+x752nQ6XWm0rSreBFCS/e3ijCivv6Bq3Zfr3c0A5KkvbWqJ25XQO8/zlbo0D0qwY/ys+UvdM7ugtExIhVv/rb7ZLmqzTR1sCQuTZwDCeUzRriwKKynoF3m0VZp7Y2bAJn5TvhrPx2DAVjzYDQpgzTfcNazsrF/LAMSU5kpFbruk3u2gVuurecWNe3t7ZRLca4pVodFWuMlkskKLadnB1T/cRUC2bhOK/VoGYFZ1Iq9Cdps1XIqqwDbTqjnUXkkFbDWpbgFYBaZVWDnQwpK5bDscdBb/tk6ELFp+lgEYpWrWvY4fiRq2fkFSNU+pEnCPCou5TuPMrnzHziFgu5zdlAqnWA1aBYhvy6EFBZ5c6H1Kk9EoA9DfBMSmSD92wW+oozVS75OvntqKIbwdX+2F9SzltgLJ83gzqPQz29aIoteWkgEbGCwLEPoZgFsee09Kxe9C7s1r8z/rNVOTk0pJObtso7DVQDTPbAi2UabXAWWZirZxE3v8G1WkhsvjVgbgb1WJW4+8ZBdQ600EI3XNLReSUaf6Zh4/ml1026luZxCoRiMj47YVNHqbqWrdDC/I3zIAf60i3vXMpB2TsitRUdUonNT3NDJUVkUvYez1DigRn/q5BH6l6aIMJLZx9HWKeMfLbphutr9mAP6sJqVOmLor4XdVj2edTaKNNUrh6eVAvb5OTSqTT0P7BA4ODlZAYs9t2aYgyua4q0gRxe/8cwbgFQ9c9NSlqvlt12QawXnkLnW5OHTz0bxxMKlHIar/Vi3IMY4k76PW0gZi7ejySGwuG9Su4vxIlekDeCUD8EeJBGo506aGe6shDEvwcpZmGdVEdBGs6VRhSj0AdfRtc0q73S41ms0+bIPFcBp2N6aSlIIewB8zAH/YNBTAsgaqOdkVc1knD+kFDvys9h7y0BSUF8VqZ5ISA7NngILk0VbZBP1l4mh05r3n75EqG432hwzA760ZqeoS9qgnz+Pv3CtHlVTnWrx4lp7HqofKUcutVmtFKPSBWDPCyJFJdf6fner8LcuW5HH0X0bw5LlWtvHXAPm/z0IIJwBe9mqFrBNsKTlTYe42REYWw9FJdxeJQ3nl1RZHs3RNOmRBmQw92iqtQtUGmXu1kZV10cNSUwzZS4F7GcAJOWN/y4EIKdTcHuqjnAceuJd+GYAyslMc6iIGYHEDsmHEah2LLSrhHoByoIQOoVD6dw08SDRzr3E0UjF4jE/eGooW/i3wX4Qrv9FabmtfU9HGtkMZOohrPp+XnBjeDICzApN25zYaDbTb7ZXCx6poXTUCMwPcAIw6bSvdZYHhSp3lzWNSeTH9rL8phSzG+GvLK5+CNHQ3Vvll2yZsZIJWk3MRD8pWzDKpriYmZWZ00lq/3y/ZwAGg1WqVPB7aZXWRFRx1np2d5OvBGfY8ImS/ViH7FW+salydTQKznMX7oW1oPLEVsBxBXTfCrPtgVNBoOjUFZdfRKzWiz8XhWcqvoRNz70QLb9LKqU1kJ94lZ43LBL3l8Svgv3j8/wLgJrGNVNeSxYGqSn7qDJq4VyCiHQZxcnJyIaZeNT6bh/m6+mgeg6Vtw6PgjMfjksOW5drkR1MKrnttKu2GSoHbhHoA3FzKFTJRv7+sGtaVMpmWZjw11KCqfOhuC5odmMXiwovUrt6MdjtBzmJOCvbyWCwWJYet4mj00RSw9UpvqkpxPJR+U+kWB0BYoFU3rboLwhT+y/I7skA/J15TpY5t7ZOazJTG8pLHl8GnoVrHsl6n8nBexiOlzTz/S8coWjPp9XPyPbIJWdNJQVTIyQ7FsJvLmmXvHr1pfWSy9JgZUyO7hV/2595spZ/RL6syJXaRZ7NZcmCoBRAvckj7Wf0OD6mez+c4OTkpa76qNHlKmLS9jQtvEX1mBrzz6Vqo0Kjp1FlQCm9Y5kvNe3obWAM7T6PZv3VaYIqXzB4Chf2sXG+xta8C+IXY1NrpBjv6xEOI7UzvbWmr44QUzm7SBa4iHfaEcTQa4datWysTS3iwZ8CLalPj/bIsK8fxaPKcmQFL5KdEM1UWqO5G15xvSshU0CVw/AWAV1eETC7gJ9wxdR1jRplektgb3XxZubfULuSmorAdHx+XUIKd3+n5novFAoPBAH//+9/LcT4UCCV5AW5Tg7J6wyut8rIRWsqt0RvhDbsZLNBcNUE5hflZ9MBeU4qkuNVqMQD6yQrwbJz1HxdF8fVWq7VCTrLJHE2n0xXfTJ1ai04rCfBlpZu83axpEyaxtaVON5Fqb0XC9XwUJJIlE63f399fyUCkurJ1nThngJQKdGsAoN/vr5AqW5/Z1rjVAYjJAl41J16529TML//9Y8X1MuOIvxhCeCFFJe5hKCnMLJVuYj/jvYQxqhx5vV5qNWoLLrwd5EU/xXL7e5wZMUacnp6uFCb2er0S3rARojd5hQLLa9A5A8TR+BnPHOv5lVymypTq0FrNuVpMjO+xfTCE8EKM8UV9xpljvn5EFPosONFkMnF9Myt4pAy4V1hPVVWsLS5ULayazfNlPL4Mjbp0TF8IoRw6pkl1O7vca72zQ8foP1oczZIze8LTbDZXCF9SQDqrVrwZnxYr43moWQH8aA3ecUzID+h4qvlI+WjKbe+xNSrdJW9U6TR1jva9MI+6MF4YblFr2wGuf7S1TeGCVO6Spo1rqpkBHdWopdzWPDE9Nh6PS+e/2WyuCKwdUsu/OeDMbiRbUKDRtgq4DUw0+CA+VhTFD1whM5L8JwDP0Q+hGlah8x4ih517JpJREQVpb29vbUfZMufLSr6fN2nu0YIqLqemUwMlRp184Gwk8TaEapbRaITJZFI+XLJue4UNnAtKeEo3iUfYR388NQfABg4SNT+XZdmf1iCjxLo9wxycJaLVgkUbNEi1JgAADJlJREFUKnNYgq2jspNzWexnm1w1Qtr2qtuzHAotxBjLqFPHKCq1ldaOecLM9RkMBiv1aARs9VkVRVES/elsAwvU8joZxXrugfc6TfDy/8+4hIoVUeTvYozXj4+P3fDaFjBSUyky7Y0PttNe6UzbatJdYySs0oyWloAPmZxoPNhoYosVbQ+otRI8j4LMg8EAzWYT7XZ7hTyGn2G0a5WEx4dblQnhBokx3syy7N0agXOTNCsijO+HEP5Xp9MpbbRtz/dGMM9mM8xmMzeKtBTcuos5NF6pl7ax//CiTCktgmJm1O4EXz2sSytLaPJYWUK/LM9zHB0dlazgtmNd2/DUr1NfTIWkqjBVSs+/b/22OpqsDeAvi8Xi6PT01HVALRio5vDw8HCt3LmqRj21U3edI83TaCmh40NixmATK5BqJzuPXbn0dXRjCiznfPWqMZT2WtrtNs3zcYzxzSGEiae0shQwt/zCk5wz7tEwedPQKHisQlUzWKV2LUXorhREnhVCUU2hU41Vu+ucATsi0ut95Xkmk8nKvE61Gh5UogqAJMtVVOpWo0pm6EkVsLUMQUqTLX/oPgCvFEXRYQ2WHaKQKnshV3673V4xgamd5PUt7uJ4mDo5wyp3QLUcwdfUYAblXFNuNdayaUmQV3rFvzXi9bIF9pmTG2SZqxzj9lSbV60PV5rnDaH8qwC+QW3maZ5U6xcbWVOga5WAVT2cXTSTqddTiWz+TX815VaktBUzFDafaX+D2k8jWU0HWpYh69Qvz/MNK2BrlqtGjvIwhPDyYrE4Ojk5WUHrNznlLHPp9XpnGnB/dawew+GwxK6sE5+qY2Pkp7OdLC5WFAWshaIAeSkqTU0tm5GPAdwP4KTq+uuAUSdFUXyVk880KqmzizlsVAXstaKl7kXOlSkoQh2KvtueSFsQqmTJnoANh8MVobXVIan+Wib5AXx1k4DV0mTiAP4uhHD9+Pi4zPpvyuirQDHarKMBrw7fKkwmk3JmAGGHFHGh4nLMk6qZHgwGGA6Ha1RVViB1gBm145Ka4SaAd9e59qzujgLwBMuJPUK3hGCWn9HqgSstdjbYQ1m52+12srrV87lYvMBatxBCmfu0Y52raEqZUZAU2BN176OWkC1v5IcxxufYwGrb1qsWivmwk5OTnRzFd9kazJIlE933qncT4wBXMisEe+0z8p6Lnp9BSJZlzwH4Yd1nGOoWJy6Pd4YQbs7n84Y2YVQdCt5a3ogrQauvzexrOqku5YZYAdX0n2ZpWBmiplM1JIstl7nsBYDrAF66ME1mHPWXiqJ4PM/zsjGi6nsMhVUta5nK1bHZTfHAVwZhWvhoBcy23dnBszbTwGdkO6lYHCmzQR8H8NJZgrezajIe/zvG+GHuJi+haov4LPPz4eGhOynkypTWA3UVi1SN5mUHqrSiar0Uh9vBwQGf1U9DCP/zrFbovPU0nwshjLvdbqmpbDTiofu6EKenpyX24/UhXgkbKjcgNy85bLVSI4VFeorAtvPps2JPwRLMHQP43HncnPMK2YsAbgC3Kwesai5P7iRkFfQbDAaYTqdu0+gu8NJelrDpn0ajUSbGU9USVUnvqvIqZhyWz/EGgBfPM9r6LBCGvdmnYozfYwu9x+3gJVpt08ZgMFgrMVFhuzpQSyOxwrbOFGEL9qoFUv9NUlrfA/DUeV2a8/pkevwfAA/SN7CNDFUhtjqfmgLxOCKujrRmU1/MdjSdNXJdLBYl98YS7H0hhPA/7oTDJLuAXfVojPGYIa5Fm6um5ap/wQZWrSq4OuppNXU3OLT1PAfdnm63S06L4xDCoyntd8+ErCiKmwA+SSeRWIs3gNSqW8tPMRqNynavK3/s7BqNgLfSLNQ1u6zE6PV6yPOcbssnY4w3vcaWeypkyx9+PsZ4g9030liwlsfk6yTz0KI8VnCenJzgAsz4a94n03wj52wqyOoNyfBgJppcDipb+mU3ADyfGlR2r80l/3wHwFcY7ahqtVWu2tvI/JouCqmTrkDb6uiSfQLj8XhlAIV15FOcZQr0asdUlmVfaTab36ly8M8ibOEuaIxvZ1l2g6zOngNfl9lHG2C1H9GDRlLsO9s+MtFCNyniOi3Ppr/K3kvlwuA9e+MO7XAHfo5TUZaC+SSAz6bW9VyK6CKFTC7+qRDCo7PZDP1+f+U9hsebQD1Lc8mFoP/g+XrK/FcnutomIasqZdeuJnaEkY1xU7bFK2NX9nIjYE/HGB+76I15x0KWgihCCM8wIDg9Pd3YLbPpQbDURIOLFIWR7vpt12RWQLxmZ900rKbghvXKrjbhlcQiu92udp0/G0J4ZBPEcemazAreErB9NMsy3Lp1a00wNt2AnTWkYbpysXptdrtS5p0CrC3ruNaE6VgcjzHRbmCdZ6AwBeerxxifBvCYCqHHN7c1Qub0CX47hHAjxoh+v39mNh+PV4OLbP01TfLuWge6l34rigLz+bysiLWmkQJDLWf/75lHIgDszwTwJIDPck3n83m5nlujyepM31gsFl9uNptfoqDp4Kq6i6+70e5Q1Wy7XqfGe6DfRW4R757sprIBhBYlUHgItC7P9xUA/6bny/O8bI/bGp8s5f84u+AzRVE8ybZ6MvltUsleZOT5GwwOiHjvSsbAYxMn11sKabe0qJYUx/PrSC8lWupGlmXfUV9QKzJs6c/W+WQVC/pQjPHZGOPRdDotydZst00V34Yunh0Kz0XK8xx5nqPZbJbNFlVlxZcpYMS6ZrPZyshE61du6sBPrc1isSgxsOVaHS+R/Oc9Hrm7cp+XgKxfB/B0jPFBmk+mNHQh1fn0xr2kFsQ2Q1DYWPeW4mi1D/aiOtgtRwi5YFniRJKZFPOhFQTPRbF1ZHoezuNcHi8AeBS3p4Xcu810iemb7xZF8Sl2z3DRvbEwHu2mDQpsCotpKtt2n+d5KXDeSMJNAyNSY4CqBJJ0Tvzj0YDa9FuKmCV1PdyUFESFe5YC+r0Qwj+lzO5rVcgQY3wMt8k6OixinM1mbj/hpiGeXu1UnXY91XLUdNpBncpUeJpQWSI5I1LpSpUaM5Xi0Q3mRYkqWHTm7QbrdrtotVpa0XojxvjUZQVE4bIT0SGEB2KM3wLwYYKNDNdTJrKq59CSwthZT6mhoLYGzrJQq+nmb+soGxUkz9R5wp6agusJYkqjae8EIR3hsf1pjPFzuF3JjLsBtG69kBmN8IUY49cbjUaDTIEMpRW+SPUQpFrHvM/aokhr6jwz7JnSVNWvd46UH0gzbitZPU1mN4VeH8FVuqYxxsezLPuPqkqY15WQySK+M8b4tRDCw8BtRm1yOVR1StdN26hJq9IYVZrSCkjqN1PdQt6ALU9Qq6il9PPECEVgnwPwLwBesptMaVTvJfFz2Ia6LQen+XiM8QkA15W0RSmOUizcVmMpIGk7pjRLkBpDk9JAKce8Sluk/EnbvW2rLew5G40GWq1WGTkv378ZY3wixvjDKsH0tNrrQsgqji8C+FcAR5x6wokgVfOBNPVkgwIvc5DSTJY9Z1MqSAUEwJqQbKLmVDjCovf8P1vgBB88xm12nX9PwS6XnWILO1CBegjgcQD/HELoEA4g7KGItjeoS83EplRJyrFOmfiqh1p1Hq+YkI3OKlDcTASVO51OeZ8xxjFuE9B9HTXomy41uNuVMucY430hhM/jdr/nkRK5KEU7tZd17lNkyFZrbdr9dZxn+7qNeFVAPU2szNY0i3Ifx8vCwm/GGF/dhTxt2MFa+jaATwP4RwDXNaFMv80yaW8yi1bL2Pr4FBDqabAqwNhmJpSTVStZqbE4FWa5oW7GGL8fQvhPJQHehSPseMPGQwAeAfCwQhGj0ahM19iReSognvlMRaRVApeCPtQXU242a2I5m4gQhOldfQ7AMwCerxv1XgnZOSLPqsVcPrS3xhg/AeBjAB7UB62JZwVPUxHiJkE6y5BRCyjbAWEcGWP9seU4vx/FGH8A4E91TPKVkN1lrM3s7gcAfBTARwD8g30wbMWzQ9ztvCgvIW0frKV2spGpTlOjA08Bc3oQfhFj/MlyIOmLde73SsjukhDVBWKXx30APgTggzHGD+B2BcjaeXUomW3h0894GJcGFhQeHbrAqNYBV28C+CWAn+P2UPhX60a+u0bsHF5nTbRvBvB+AO8D8F4A7wFwv8XR1InXGUjWVGlxJB15O99g+fmXQwi/BfAbAL8G8CsAfznvhts5a3PVqY1DAO8C8A4Ab8ft6RpvAfAmAG8EcA1AD0AXQA4gAIgAZgBGAPoAbgH4G4C/AvgzgFcA/BHAHwD8fttxrLt9/H8+vuRI9iyzmwAAAABJRU5ErkJggg==">');
        return $this;
    }

}
