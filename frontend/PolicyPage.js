import React, { useEffect, useRef } from 'react';
import { BackHandler, Platform, Pressable, ScrollView, StyleSheet, Text, View, useWindowDimensions } from 'react-native';
import { policies } from './policies';

export default function PolicyPage({ selectedId, onSelect, onHome, onComplaint }) {
  const scroll = useRef(null);
  const layoutY = useRef(0);
  const contentY = useRef(0);
  const scrollToContent = useRef(false);
  const { width } = useWindowDimensions();
  const wide = width >= 900;
  const current = policies.find(policy => policy.id === selectedId) || policies[0];

  useEffect(() => {
    if (Platform.OS === 'web') return undefined;
    const handler = BackHandler.addEventListener('hardwareBackPress', () => { onHome(); return true; });
    return () => handler.remove();
  }, [onHome]);

  useEffect(() => {
    if (!scrollToContent.current) return;
    scrollToContent.current = false;
    const frame = requestAnimationFrame(() => {
      scroll.current?.scrollTo({ y: Math.max(0, layoutY.current + contentY.current - 12), animated: true });
    });
    return () => cancelAnimationFrame(frame);
  }, [current.id]);

  function selectPolicy(id) {
    if (id === current.id) return;
    scrollToContent.current = !wide;
    onSelect(id);
  }

  return <View style={styles.root}>
    <View style={[styles.header, !wide && styles.headerMobile]}><Pressable accessibilityRole="button" onPress={onHome} style={styles.brand}><View style={styles.brandMark}><Text style={styles.markText}>SM</Text></View><View><Text style={styles.brandTitle}>TRẠI GÀ SIÊU MÔN</Text><Text style={styles.brandSub}>TỪ TRẠI ĐẾN BÀN ĂN</Text></View></Pressable><Pressable accessibilityRole="button" onPress={onHome}><Text style={styles.back}>← Trở về cửa hàng</Text></Pressable></View>
    <ScrollView ref={scroll} contentContainerStyle={styles.scroll}>
      <View style={styles.hero}><Text style={styles.eyebrow}>TRẠI GÀ SIÊU MÔN / CHÍNH SÁCH</Text><Text style={[styles.title, !wide && styles.titleMobile]}>Mua hàng an tâm, <Text style={styles.accent}>thông tin rõ ràng.</Text></Text><Text style={styles.intro}>Tìm hiểu điều kiện đặt hàng trực tiếp D2C và cách trại tiếp nhận, xử lý phản hồi của khách hàng.</Text></View>
      {/* <View style={styles.notice}><Text style={styles.noticeTitle}>Thông tin áp dụng</Text><Text style={styles.noticeText}>Đây là nội dung dự kiến cho website demo. Giá, phí giao hàng, thời hạn xử lý và thông tin liên hệ chính thức cần được trại xác nhận trước khi áp dụng.</Text></View> */}
      <View style={[styles.layout, wide && styles.layoutWide]} onLayout={event => { layoutY.current = event.nativeEvent.layout.y; }}><View style={[styles.menu, wide && styles.menuWide]}><Text style={styles.menuLabel}>DANH MỤC CHÍNH SÁCH</Text>{policies.map((item, index) => <Pressable key={item.id} accessibilityRole="button" accessibilityState={{ selected: item.id === current.id }} onPress={() => selectPolicy(item.id)} style={[styles.menuItem, item.id === current.id && styles.menuActive]}><Text style={[styles.menuNumber, item.id === current.id && styles.menuActiveText]}>{String(index + 1).padStart(2, '0')}</Text><Text style={[styles.menuText, item.id === current.id && styles.menuActiveText]}>{item.title}</Text><Text style={styles.menuArrow}>→</Text></Pressable>)}</View>
        <View style={styles.content} onLayout={event => { contentY.current = event.nativeEvent.layout.y; }}><Text style={styles.contentEyebrow}>CHÍNH SÁCH NHÀ MÔN</Text><Text style={styles.contentTitle}>{current.title}</Text><Text style={styles.contentIntro}>{current.body}</Text><View style={styles.divider} />{current.sections.map(([heading, body], index) => <View style={styles.section} key={heading}><Text style={styles.sectionLabel}>{String(index + 1).padStart(2, '0')} / {heading}</Text><Text style={styles.body}>{body}</Text></View>)}<View style={styles.help}><Text style={styles.helpTitle}>Cần hỗ trợ thêm?</Text><Text style={styles.helpBody}>Gửi thông tin để trại tiếp nhận phản ánh hoặc trao đổi về nhu cầu mua hàng của bạn.</Text><View style={styles.actions}><Pressable accessibilityRole="button" style={styles.primary} onPress={onComplaint}><Text style={styles.primaryText}>Gửi phản ánh  →</Text></Pressable></View></View></View>
      </View>
      <View style={styles.footer}><Text style={styles.footerBrand}>TRẠI GÀ SIÊU MÔN</Text><Text style={styles.footerCopy}>Đặt hàng trực tiếp từ trại đến gia đình</Text><Pressable onPress={onHome}><Text style={styles.footerLink}>Trở về cửa hàng →</Text></Pressable></View>
    </ScrollView>
  </View>;
}

const styles = StyleSheet.create({
  root:{flex:1,backgroundColor:'#faf9f3'},header:{minHeight:80,backgroundColor:'#fffef9',paddingHorizontal:'7%',flexDirection:'row',justifyContent:'space-between',alignItems:'center',gap:15,borderBottomWidth:1,borderColor:'#e4e7dd'},headerMobile:{paddingHorizontal:18,minHeight:70},brand:{flexDirection:'row',alignItems:'center',gap:10,flexShrink:1},brandMark:{width:40,height:40,borderWidth:1,borderColor:'#214933',borderRadius:22,justifyContent:'center',alignItems:'center'},markText:{fontSize:14,fontWeight:'700',color:'#214933'},brandTitle:{fontSize:12,fontWeight:'800',letterSpacing:1.2,color:'#183d2b'},brandSub:{fontSize:9,letterSpacing:1.2,color:'#7c8879',marginTop:3},back:{fontSize:13,fontWeight:'700',color:'#28543a'},scroll:{flexGrow:1},hero:{paddingHorizontal:'9%',paddingTop:65,paddingBottom:44,backgroundColor:'#e9eee2'},eyebrow:{fontSize:11,letterSpacing:1.5,fontWeight:'700',color:'#55744e'},title:{color:'#183d2b',fontSize:47,lineHeight:59,fontWeight:'700',maxWidth:850,marginTop:15},titleMobile:{fontSize:32,lineHeight:42},accent:{color:'#a8743e'},intro:{fontSize:15,lineHeight:24,color:'#5c6a5b',maxWidth:690,marginTop:15},notice:{marginHorizontal:'9%',marginTop:26,padding:18,backgroundColor:'#f8f0db',borderLeftWidth:3,borderColor:'#c3934a'},noticeTitle:{fontSize:14,fontWeight:'700',color:'#614b27',marginBottom:5},noticeText:{fontSize:13,lineHeight:21,color:'#665d4a'},layout:{paddingHorizontal:'9%',paddingVertical:38,gap:32},layoutWide:{flexDirection:'row',alignItems:'flex-start',gap:48},menu:{width:'100%',backgroundColor:'#fffef9',borderWidth:1,borderColor:'#e4e7dd',padding:14},menuWide:{width:290},menuLabel:{fontSize:10,fontWeight:'700',letterSpacing:1.1,color:'#788578',paddingHorizontal:10,paddingVertical:12},menuItem:{flexDirection:'row',alignItems:'center',gap:10,paddingHorizontal:10,paddingVertical:12,borderBottomWidth:1,borderColor:'#edf0e9'},menuActive:{backgroundColor:'#e7eee2'},menuNumber:{fontSize:11,color:'#9ca89b',width:20},menuText:{fontSize:13,lineHeight:19,color:'#556257',flex:1},menuActiveText:{color:'#183d2b',fontWeight:'700'},menuArrow:{fontSize:15,color:'#9aab97'},content:{flex:1,minWidth:0,backgroundColor:'#fffef9',borderWidth:1,borderColor:'#e4e7dd',padding:25},contentEyebrow:{fontSize:10,letterSpacing:1.2,fontWeight:'700',color:'#6b8068'},contentTitle:{fontSize:30,lineHeight:39,fontWeight:'700',color:'#183d2b',marginTop:10},contentIntro:{fontSize:15,lineHeight:24,color:'#7a8376',marginTop:10},divider:{height:1,backgroundColor:'#e6eae1',marginTop:23,marginBottom:6},section:{paddingTop:24},sectionLabel:{fontSize:16,lineHeight:24,color:'#1d4931',fontWeight:'700',marginBottom:8},body:{fontSize:15,lineHeight:26,color:'#4e5b4f'},help:{marginTop:34,padding:22,backgroundColor:'#edf2e8'},helpTitle:{fontSize:18,fontWeight:'700',color:'#183d2b'},helpBody:{fontSize:14,lineHeight:22,color:'#607061',marginTop:8},actions:{flexDirection:'row',gap:10,flexWrap:'wrap',marginTop:17},primary:{backgroundColor:'#183d2b',paddingHorizontal:16,paddingVertical:13},primaryText:{color:'#fff',fontSize:13,fontWeight:'700'},secondary:{borderWidth:1,borderColor:'#9cac99',paddingHorizontal:16,paddingVertical:12},secondaryText:{color:'#183d2b',fontSize:13,fontWeight:'700'},footer:{backgroundColor:'#183d2b',paddingHorizontal:'9%',paddingVertical:30,gap:8},footerBrand:{color:'#f5f1e6',fontSize:14,letterSpacing:1,fontWeight:'700'},footerCopy:{color:'#cbd6c8',fontSize:12,lineHeight:20},footerLink:{color:'#e4c493',fontSize:13,fontWeight:'700',marginTop:5},
});
