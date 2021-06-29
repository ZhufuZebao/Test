<template>
  <div>
    <div id="Gmaps"></div>
  </div>
</template>
<script>
  import RemoteJs from "../../pages/project/ProjectRemote";
  import '../../mixins/Markerclusterer.js'
  importScript("https://maps.googleapis.com/maps/api/js?key=" + process.env.MIX_GOOGLE_MAP_KEY);

  export function importScript (path, success, error) {
    var oS = document.createElement('script')
    oS.src = path
    document.getElementsByTagName('head')[0].appendChild(oS)
    oS.onload = function () {
      success && success()
    }

    oS.onerror = function () {
      error && error()
    }
  }
  export default {
    components: {
      RemoteJs
    },
    data: function () {
      return {
        map: "",
        flag: false,
        markerClusterer: null,
        gData:[],
        x: 0,
        y: 0,
        z: 0,
        projects:[],

        location:'',
        bounds:'',
      };
    },

    methods: {
      loadRongJs() {
        this.flag = true;
        this.map = new google.maps.Map(document.getElementById("Gmaps"), {
          streetViewControl:true,
          zoom: 8,
          center: { lat: 35.6898, lng: 139.6932 },
          mapTypeId: google.maps.MapTypeId.ROADMAP,

          zoomControl:true,
          zoomControlOptions: {
            style:google.maps.ZoomControlStyle.DEFAULT
          },
        });

        //map marker icon background
        MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_PATH_ =
            "https://raw.githubusercontent.com/googlemaps/v3-utility-library/master/markerclustererplus/images/m";

        if (this.projects.length !== 0) {
          for (let i = 0; i < this.projects.length; i ++){
            //DB col latitudeとlongitude存在の場合
            if (this.projects[i].latitude != null && this.projects[i].longitude != null) {
              this.gData.push({Lat: this.projects[i].latitude, Lon: this.projects[i].longitude,name:'aaa'});
            } else {
              if (this.projects[i].address){
                //project lat lon を 計算
                this.getLatLon(this.projects[i].address,this.projects[i].place_name);
              }
            }
          }
        }
      },
      setMaps() {
        //markers
        if (this.flag) {
          if (this.markerClusterer) {
            this.markerClusterer.clearMarkers();
          }
          let markers = this.gData.map(item => {
            let projName = item.name;
            let latlng = new google.maps.LatLng(item.Lat, item.Lon);
            let marker = new google.maps.Marker({
              position: latlng,
              title: ''
            });
            //add infowindow
            let infowindow = new google.maps.InfoWindow({
              // content: `<div>
              //             <p>ID:helloworld</p>
              //             <p>Place:WF</p>
              //           </div>`
              content: '案件名:'+item.projName + '<br>'+
                  '住所:'+item.name,
            });
            //always open
            infowindow.open(this.map, marker);
            google.maps.event.addListener(marker, 'click', function () {
              infowindow.open(this.map, marker);
              this.map.setZoom(9);
              this.map.setCenter(marker.getPosition());
            });
            return marker;
          });
          this.markerClusterer= new MarkerClusterer(this.map, markers,{ maxZoom: 15 });
        }
      },
      //address to lat lon
      getLatLon(location,projName) {
        // let address = location;
        let $this = this;
        let geocoder = new google.maps.Geocoder();
        this.bounds = new google.maps.LatLngBounds();
        geocoder.geocode({'address': location}, function (results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            let lat = results[0].geometry.location.lat();
            let lng = results[0].geometry.location.lng();
            let result = {"lat":lat,"lng":lng};
            $this.gData.push({Lat: lat, Lon: lng, name:location,projName:projName});

            //google markersを設定
            $this.setMaps($this.gData);

            //地図の中心点を設定
            $this.map.setCenter($this.getMarkerCenter($this.gData));

            $this.bounds.extend(new google.maps.LatLng(lat, lng));
            $this.map.fitBounds($this.bounds);
            return result;
          } else {
            alert("Geocode was not successful for the following reason: " + status);
            return 'error'
          }
        });
      },

      //地図の中心点を取得します
      //markerによると中心点を取得します
      getMarkerCenter(markers){
        for(let i = 0; i < markers.length ; i++){
          let lat = 0;
          let lon = 0;
          let x1 = 0;
          let y1 = 0;
          let z1 = 0;
          lat = markers[i].Lat * Math.PI / 180;
          lon = markers[i].Lon * Math.PI / 180;
          x1 = Math.cos(lat) * Math.cos(lon);
          y1 = Math.cos(lat) * Math.sin(lon);
          z1 = Math.sin(lat);
          this.x += x1;
          this.y += y1;
          this.z += z1;
        }
        this.x = this.x / markers.length;
        this.y = this.y / markers.length;
        this.z = this.z / markers.length;
        let lon = Math.atan2(this.y, this.x);
        let hyp = Math.sqrt(this.x * this.x + this.y * this.y);
        let lat = Math.atan2(this.z, hyp);

        return new google.maps.LatLng(lat * 180 / Math.PI,lon * 180 / Math.PI);
      },

      //案件データ取得
      fetchProjects() {
        const loading = this.$loading({lock: true, background: 'rgba(0, 0, 0, 0.7)'});
        axios.post('/api/getMapProjectList').then((res) => {
          this.formatArray(res);
        }).catch(error => {
          loading.close();
        });
        loading.close();
      },

      //案件データを処理
      formatArray(res) {
        let data = [];
        if (res.data) {
          for (let i = 0; i < res.data.length; i++) {
            let pro = res.data[i];
            //施工期間の日付格式を修正
            if(pro.st_date){
              pro.st_date = (new Date(pro.st_date)).getFullYear() + "/" +
                  ((new Date(pro.st_date)).getMonth() + 1) + "/" +
                  (new Date(pro.st_date)).getDate();
            }
            if(pro.ed_date){
              pro.ed_date = (new Date(pro.ed_date)).getFullYear() + "/" +
                  ((new Date(pro.ed_date)).getMonth() + 1) + "/" +
                  (new Date(pro.ed_date)).getDate();
            }
            if (!pro.customer) {
              pro.customer = {};
            }
            if (!pro.customer_office) {
              pro.customer_office = {};
            }
            if (!pro.user) {
              pro.user = {};
            }
            //施工期間によって、現時点がどのくらい位置を表示する
            let $pro_course = ((new Date(pro.ed_date)) - (new Date(pro.st_date)));
            let $now_course = ((new Date()) - (new Date(pro.st_date)));
            pro.progress = $now_course / $pro_course;
            if ($now_course > $pro_course) {
              pro.progress = 100
            } else if ($now_course <= 0) {
              pro.progress = 0
            } else if ($pro_course <= 0) {
              pro.progress = 100
            } else {
              pro.progress = $now_course / $pro_course * 100
            }
            data.push(pro);
          }
          this.projects = data;
          this.loadRongJs();
        }
      },
    },
    created() {
      this.fetchProjects();

    }
  };
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
  #Gmaps {
    width: 100%;
    height: 40rem;
    border-radius: 5px;
  }
</style>
